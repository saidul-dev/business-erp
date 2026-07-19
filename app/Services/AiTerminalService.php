<?php

namespace App\Services;

use App\Models\LedgerAccount;
use App\Models\LedgerTransactionLine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Backs the Admin > AI Terminal. Gemini drives the conversation with
 * function calling: it can chat directly for small talk, call one of four
 * fixed metric tools, or fall back to a validated read-only SQL query (see
 * AiSqlGuard) for anything else. Every figure always comes from a real DB
 * query — Gemini only decides which tool to call and phrases the final
 * sentence, in whatever language/script the admin asked in. If Gemini is
 * unreachable/misconfigured, a keyword fallback (fixed 4 intents, Bangla
 * replies) keeps the terminal usable.
 */
class AiTerminalService
{
    protected const MAX_ROUNDS = 3;

    public function __construct(protected AiSqlGuard $sqlGuard)
    {
    }

    public function answer(string $question, ?int $siteId): string
    {
        $apiKey = config('services.gemini.api_key');

        if (! $apiKey) {
            return $this->legacyAnswer($question, $siteId);
        }

        try {
            $reply = $this->converse($question, $siteId, $apiKey);

            if ($reply !== null) {
                return $reply;
            }
        } catch (\Throwable $e) {
            Log::warning('AI terminal Gemini conversation threw', ['message' => $e->getMessage()]);
        }

        return $this->legacyAnswer($question, $siteId);
    }

    protected function converse(string $question, ?int $siteId, string $apiKey): ?string
    {
        $model = config('services.gemini.model', 'gemini-flash-latest');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $contents = [
            ['role' => 'user', 'parts' => [['text' => $question]]],
        ];

        $tools = [['functionDeclarations' => $this->toolDeclarations()]];
        $systemInstruction = ['parts' => [['text' => $this->systemPrompt($siteId)]]];

        for ($round = 0; $round < self::MAX_ROUNDS; $round++) {
            $response = Http::timeout(15)->post($endpoint, [
                'systemInstruction' => $systemInstruction,
                'contents' => $contents,
                'tools' => $tools,
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 400,
                    'thinkingConfig' => ['thinkingBudget' => 0],
                ],
            ]);

            if (! $response->successful()) {
                Log::warning('AI terminal Gemini call failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            $part = data_get($response->json(), 'candidates.0.content.parts.0', []);
            $functionCall = $part['functionCall'] ?? null;

            if ($functionCall) {
                $name = $functionCall['name'] ?? '';
                $args = $functionCall['args'] ?? [];

                $contents[] = ['role' => 'model', 'parts' => [['functionCall' => ['name' => $name, 'args' => $args]]]];

                $result = $this->executeTool($name, $args, $siteId, $question);

                $contents[] = [
                    'role' => 'function',
                    'parts' => [[
                        'functionResponse' => [
                            'name' => $name,
                            'response' => ['name' => $name, 'content' => $result],
                        ],
                    ]],
                ];

                continue;
            }

            $text = trim((string) ($part['text'] ?? ''));

            return $text !== '' ? $text : null;
        }

        return null;
    }

    protected function executeTool(string $name, array $args, ?int $siteId, string $question): array
    {
        return match ($name) {
            'today_sale' => ['metric' => 'today_sale', 'amount_bdt' => $this->money($this->todaySaleAmount($siteId))],
            'today_purchase' => ['metric' => 'today_purchase', 'amount_bdt' => $this->money($this->todayPurchaseAmount($siteId))],
            'today_profit_loss' => $this->todayProfitLossResult($siteId),
            'balance' => ['metric' => 'balance', 'amount_bdt' => $this->money($this->cashBalanceAmount($siteId))],
            'run_sql' => $this->runSql((string) ($args['query'] ?? ''), $siteId, $question),
            default => ['error' => "Unknown tool: {$name}"],
        };
    }

    protected function runSql(string $query, ?int $siteId, string $question): array
    {
        $result = $this->sqlGuard->run($query, $siteId);

        Log::info('AI terminal SQL fallback', [
            'question' => $question,
            'site_id' => $siteId,
            'sql' => $query,
            'error' => $result['error'] ?? null,
        ]);

        return $result;
    }

    protected function toolDeclarations(): array
    {
        return [
            ['name' => 'today_sale', 'description' => "Get today's total sales amount in BDT."],
            ['name' => 'today_purchase', 'description' => "Get today's total purchase amount in BDT."],
            ['name' => 'today_profit_loss', 'description' => "Get today's net profit or loss in BDT."],
            ['name' => 'balance', 'description' => 'Get the current total cash + bank balance in BDT.'],
            [
                'name' => 'run_sql',
                'description' => 'Run a single read-only MySQL SELECT query against the schema below to answer a business question the other tools do not cover. Must be exactly one SELECT statement using only the listed tables.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'query' => ['type' => 'STRING', 'description' => 'A single MySQL SELECT statement.'],
                    ],
                    'required' => ['query'],
                ],
            ],
        ];
    }

    protected function systemPrompt(?int $siteId): string
    {
        $site = $siteId ? Site::find($siteId) : null;
        $siteLabel = $site?->name ?? 'All Sites';
        $today = now()->toDateString();
        $schema = $this->sqlGuard->schemaDescription();

        return <<<PROMPT
            You are the AI Terminal inside a shop's business ERP, helping the admin with quick answers.

            Rules:
            - Reply in the SAME language/script the admin used to ask — Bengali script, Banglish (Romanized Bengali), or English. Match them exactly.
            - For greetings or small talk, just reply naturally and briefly — no tool call needed.
            - For any question about business data (sales, purchases, profit/loss, balance, stock, products, employees, attendance, etc.), you MUST use a tool to get the real number — never guess or invent a figure.
            - Prefer today_sale / today_purchase / today_profit_loss / balance when they directly answer the question. For anything else that needs data, call run_sql with a SELECT query using only the schema below.
            - If a tool result contains an "error", briefly tell the admin (in their language) that you couldn't safely answer that — don't expose raw SQL or internal details.
            - Keep replies short — a sentence or two, like a terminal reply.

            Context:
            - Today's date: {$today}
            - Current site: {$siteLabel}

            Schema available to run_sql (MySQL, read-only):
            {$schema}
            PROMPT;
    }

    // --- legacy keyword-fallback path (used when Gemini is unreachable) ---

    protected function legacyAnswer(string $question, ?int $siteId): string
    {
        $intent = $this->keywordFallback($question);

        return match ($intent) {
            'today_sale' => 'Ajker Sale: '.$this->money($this->todaySaleAmount($siteId)).' Taka',
            'today_purchase' => 'Ajker Purchase: '.$this->money($this->todayPurchaseAmount($siteId)).' Taka',
            'today_profit_loss' => $this->legacyProfitLossReply($siteId),
            'balance' => 'Ajker Balance: '.$this->money($this->cashBalanceAmount($siteId)).' Taka',
            default => "Bujhte parlam na. Apni jiggasa korte paren: 'today sale koto', 'today purchase koto', 'today profit loss koto', othoba 'balance koto'.",
        };
    }

    protected function legacyProfitLossReply(?int $siteId): string
    {
        $net = $this->todayProfitLossAmount($siteId);

        return $net >= 0
            ? 'Ajker Profit: '.$this->money($net).' Taka'
            : 'Ajker Loss: '.$this->money(abs($net)).' Taka';
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

    // --- shared data helpers ---

    protected function todaySaleAmount(?int $siteId): float
    {
        return (float) Sale::whereDate('order_date', today())
            ->where('status', '!=', 'cancelled')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->sum('total_amount');
    }

    protected function todayPurchaseAmount(?int $siteId): float
    {
        return (float) Purchase::whereDate('order_date', today())
            ->where('status', '!=', 'cancelled')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->sum('total_amount');
    }

    /**
     * Net profit for today = credits minus debits across every income_expense
     * ledger line dated today — algebraically equivalent to the categorized
     * revenue/COGS/expense breakdown ProfitLossController builds, since each
     * account's nature-flip and the netProfit sign-flip cancel out (verified
     * against BalanceSheetController's netProfit formula).
     */
    protected function todayProfitLossAmount(?int $siteId): float
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

        return round($net, 2);
    }

    protected function todayProfitLossResult(?int $siteId): array
    {
        $net = $this->todayProfitLossAmount($siteId);

        return [
            'metric' => 'today_profit_loss',
            'is_profit' => $net >= 0,
            'amount_bdt' => $this->money(abs($net)),
        ];
    }

    /**
     * Current cash + bank balance, scoped to the selected site the same way
     * cash_bank ledger accounts themselves are site-scoped (see
     * LedgerAccount::booted()) — "All Sites" sums every site's drawer/bank.
     */
    protected function cashBalanceAmount(?int $siteId): float
    {
        return LedgerAccount::where('group', 'cash_bank')
            ->where('status', true)
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->get()
            ->sum(fn (LedgerAccount $account) => $account->balance());
    }

    protected function money(float $amount): string
    {
        $formatted = number_format($amount, 2);

        return str_ends_with($formatted, '.00') ? substr($formatted, 0, -3) : $formatted;
    }
}
