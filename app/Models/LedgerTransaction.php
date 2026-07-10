<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerTransaction extends Model
{
    /**
     * Fixed voucher types. Every module that posts to the ledger (Purchase,
     * Sale, Payment, Expense, ...) uses one of these — never a free-form
     * string — so voucher numbering and future reporting can rely on it.
     */
    public const TYPES = [
        'purchase',
        'sale',
        'payment_out',
        'payment_in',
        'expense',
        'income',
        'opening_balance',
        'journal',
    ];

    /**
     * Voucher number prefix per type — see LedgerService::nextVoucherNo().
     */
    public const PREFIXES = [
        'purchase' => 'PUR',
        'sale' => 'SAL',
        'payment_out' => 'PAY',
        'payment_in' => 'REC',
        'expense' => 'EXP',
        'income' => 'INC',
        'opening_balance' => 'OB',
        'journal' => 'JNL',
    ];

    protected $fillable = [
        'voucher_no',
        'date',
        'type',
        'reference_type',
        'reference_id',
        'narration',
        'site_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(LedgerTransactionLine::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
