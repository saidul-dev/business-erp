<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    /**
     * The 5 functional groups every account belongs to.
     */
    public const GROUPS = ['cash_bank', 'party', 'inventory', 'income_expense', 'equity_adjustment'];

    public const NATURES = ['debit', 'credit'];

    /**
     * Stable `code` values for the auto-seeded system accounts — see
     * LedgerAccountSeeder. Referenced by LedgerService::post() and any
     * module posting to the ledger, so callers never depend on the
     * human-editable `name`.
     */
    public const SYSTEM_CODES = [
        'cash_in_hand' => 'Cash in Hand',
        'accounts_receivable' => 'Accounts Receivable',
        'accounts_payable' => 'Accounts Payable',
        'inventory' => 'Inventory',
        'opening_balance_equity' => 'Opening Balance Equity',
        'purchase_expense' => 'Purchase Expense',
        'sales_revenue' => 'Sales Revenue',
        'cost_of_goods_sold' => 'Cost of Goods Sold',
        'discount_allowed' => 'Discount Allowed',
        'rent_expense' => 'Rent Expense',
        'utility_expense' => 'Utility Expense',
        'salary_expense' => 'Salary Expense',
        'office_expense' => 'Office Expense',
        'other_expense' => 'Other Expense',
        'interest_income' => 'Interest Income',
        'rental_income' => 'Rental Income',
        'commission_income' => 'Commission Income',
        'other_income' => 'Other Income',
    ];

    protected $fillable = [
        'name',
        'code',
        'group',
        'nature',
        'is_system',
        'site_id',
        'status',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Only the cash_bank group is ever site-specific (a branch's own
        // cash drawer/bank account) — every other group is company-wide.
        static::saving(function (LedgerAccount $account) {
            if ($account->group !== 'cash_bank') {
                $account->site_id = null;
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(LedgerTransactionLine::class);
    }

    /**
     * Signed balance from ledger history — never a stored counter.
     * Positive = a debit-normal account (asset/expense) with a normal
     * balance, or a credit-normal account (liability/equity/income) with a
     * normal balance; the `nature` flip keeps the sign meaningful either way.
     */
    public function balance(): float
    {
        $raw = (float) $this->lines()->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as bal')->value('bal');

        return $this->nature === 'credit' ? -$raw : $raw;
    }
}
