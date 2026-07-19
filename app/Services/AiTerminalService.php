<?php

namespace App\Services;

use App\Models\LedgerAccount;
use App\Models\LedgerTransactionLine;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Backs the Admin > AI Terminal: Gemini only classifies the admin's free-text
 * query (Bengali/Banglish/English) into one of a fixed set of known metrics —
 * it never generates the figure itself. The actual number always comes from
 * a real DB query below, so the terminal can't hallucinate a balance/sale
 * amount. If Gemini is unreachable or misconfigured, a keyword fallback
 * keeps the terminal usable for the common phrasings.
 */
class AiTerminalService
{
    protected const INTENTS = ['today_sale', 'today_purchase', 'today_profit_loss', 'balance', 'unknown'];

    public function answer(string $question, ?int $siteId): string
    {
        $intent = $this->classify($question);

        return match ($intent) {
            'today_sale' => $this->todaySale($siteId),
            'today_purchase' => $this->todayPurchase($siteId),
            'today_profit_loss' => $this->todayProfitLoss($siteId),
            'balance' => $this->cashBalance($siteId),
            default => "Bujhte parlam na. Apni jiggasa korte paren: 'today sale koto', 'today purchase koto', 'today profit loss koto', othoba 'balance koto'.",
        };
    }

    protected function classify(string $question): string
    {
        $apiKey = config('services.gemini.api_key');

        if (! $apiKey) {
            return $this->keywordFallback($question);
        }

        $model = config('services.gemini.model', 'gemini-flash-latest');

        $prompt = <<<PROMPT
            You classify a short query from a shop admin (written in Bengali script, Banglish, or English) into exactly one of these categories:
            - today_sale: asking about today's total sales/revenue
            - today_purchase: asking about today's total purchases
            - today_profit_loss: asking about today's profit or loss
            - balance: asking about the current cash/bank balance
            - unknown: anything else, or unclear

            Reply with ONLY the category name (one of: today_sale, today_purchase, today_profit_loss, balance, unknown). No punctuation, no explanation.

            Query: "{$question}"
            PROMPT;

        try {
            $response = Http::timeout(10)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0,
                        'maxOutputTokens' => 20,
                        // Flash-tier "thinking" is wasted on a one-word
                        // classification and otherwise burns the whole
                        // token budget on reasoning, leaving no room for
                        // the actual answer (empty text, MAX_TOKENS).
                        'thinkingConfig' => ['thinkingBudget' => 0],
                    ],
                ]
            );

            if (! $response->successful()) {
                Log::warning('AI terminal Gemini call failed', ['status' => $response->status(), 'body' => $response->body()]);

                return $this->keywordFallback($question);
            }

            $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text'));
            $text = strtolower(preg_replace('/[^a-z_]/i', '', $text));

            return in_array($text, self::INTENTS, true) ? $text : 'unknown';
        } catch (\Throwable $e) {
            Log::warning('AI terminal Gemini call threw', ['message' => $e->getMessage()]);

            return $this->keywordFallback($question);
        }
    }

    protected function keywordFallback(string $question): string
    {
        $q = mb_strtolower($question);

        return match (true) {
            str_contains($q, 'purchase') || str_contains($q, 'kroy') || str_contains($q, 'kena') => 'today_purchase',
            str_contains($q, 'profit') || str_contains($q, 'loss') || str_contains($q, 'labh') || str_contains($q, 'lav') || str_contains($q, 'khoti') => 'today_profit_loss',
            str_contains($q, 'balance') => 'balance',
            str_contains($q, 'sale') || str_contains($q, 'bikroy') => 'today_sale',
            default => 'unknown',
        };
    }

    protected function todaySale(?int $siteId): string
    {
        $amount = (float) Sale::whereDate('order_date', today())
            ->where('status', '!=', 'cancelled')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->sum('total_amount');

        return 'Ajker Sale: '.$this->money($amount).' Taka';
    }

    protected function todayPurchase(?int $siteId): string
    {
        $amount = (float) Purchase::whereDate('order_date', today())
            ->where('status', '!=', 'cancelled')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->sum('total_amount');

        return 'Ajker Purchase: '.$this->money($amount).' Taka';
    }

    /**
     * Net profit for today = credits minus debits across every income_expense
     * ledger line dated today — algebraically equivalent to the categorized
     * revenue/COGS/expense breakdown ProfitLossController builds, since each
     * account's nature-flip and the netProfit sign-flip cancel out (verified
     * against BalanceSheetController's netProfit formula).
     */
    protected function todayProfitLoss(?int $siteId): string
    {
        $net = (float) LedgerTransactionLine::query()
            ->join('ledger_transactions', 'ledger_transactions.id', '=', 'ledger_transaction_lines.ledger_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'ledger_transaction_lines.ledger_account_id')
            ->whereDate('ledger_transactions.date', today())
            ->where('ledger_accounts.group', 'income_expense')
            ->where('ledger_accounts.status', true)
            ->when($siteId, fn ($q) => $q->where('ledger_transactions.site_id', $siteId))
            ->selectRaw('COALESCE(SUM(ledger_transaction_lines.credit) - SUM(ledger_transaction_lines.debit), 0) as net')
            ->value('net');

        $net = round($net, 2);

        return $net >= 0
            ? 'Ajker Profit: '.$this->money($net).' Taka'
            : 'Ajker Loss: '.$this->money(abs($net)).' Taka';
    }

    /**
     * Current cash + bank balance, scoped to the selected site the same way
     * cash_bank ledger accounts themselves are site-scoped (see
     * LedgerAccount::booted()) — "All Sites" sums every site's drawer/bank.
     */
    protected function cashBalance(?int $siteId): string
    {
        $balance = LedgerAccount::where('group', 'cash_bank')
            ->where('status', true)
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->get()
            ->sum(fn (LedgerAccount $account) => $account->balance());

        return 'Ajker Balance: '.$this->money($balance).' Taka';
    }

    protected function money(float $amount): string
    {
        $formatted = number_format($amount, 2);

        return str_ends_with($formatted, '.00') ? substr($formatted, 0, -3) : $formatted;
    }
}
