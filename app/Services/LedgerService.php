<?php

namespace App\Services;

use App\Models\LedgerAccount;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The only place allowed to write ledger_transactions/ledger_transaction_lines.
 * Every module (Purchase, Sale, Payment, Expense, ...) posts its financial
 * side-effect through here instead of touching a balance column directly —
 * see docs/accounting-foundation.md.
 */
class LedgerService
{
    /**
     * @param  array{
     *     type: string,
     *     date?: \DateTimeInterface|string,
     *     branch_id?: int|null,
     *     narration?: string|null,
     *     reference?: \Illuminate\Database\Eloquent\Model|null,
     *     created_by?: int|null,
     *     lines: array<array{account: string|LedgerAccount, party_id?: int|null, debit?: float, credit?: float}>,
     * }  $data
     */
    public static function post(array $data): LedgerTransaction
    {
        if (! in_array($data['type'] ?? null, LedgerTransaction::TYPES, true)) {
            throw new InvalidArgumentException("Unknown ledger transaction type [{$data['type']}].");
        }

        $lines = $data['lines'] ?? [];

        if (count($lines) < 2) {
            throw new InvalidArgumentException('A ledger transaction needs at least two lines.');
        }

        $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);

        if ($totalDebit !== $totalCredit) {
            throw new InvalidArgumentException("Ledger transaction is unbalanced: debit {$totalDebit} != credit {$totalCredit}.");
        }

        return DB::transaction(function () use ($data, $lines, $totalDebit) {
            $transaction = LedgerTransaction::create([
                'voucher_no' => static::nextVoucherNo($data['type']),
                'date' => $data['date'] ?? now(),
                'type' => $data['type'],
                'reference_type' => isset($data['reference']) ? $data['reference']->getMorphClass() : null,
                'reference_id' => $data['reference']?->getKey(),
                'narration' => $data['narration'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            foreach ($lines as $line) {
                $account = $line['account'] instanceof LedgerAccount
                    ? $line['account']
                    : LedgerAccount::where('code', $line['account'])->firstOrFail();

                $transaction->lines()->create([
                    'ledger_account_id' => $account->id,
                    'party_id' => $line['party_id'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Global per-type sequence — e.g. PUR-000001. Simple count+1, not a
     * locked sequence table: acceptable for this app's low-concurrency,
     * single-company usage; revisit if that ever changes.
     */
    protected static function nextVoucherNo(string $type): string
    {
        $prefix = LedgerTransaction::PREFIXES[$type] ?? strtoupper(substr($type, 0, 3));
        $count = LedgerTransaction::where('type', $type)->lockForUpdate()->count();

        return sprintf('%s-%06d', $prefix, $count + 1);
    }
}
