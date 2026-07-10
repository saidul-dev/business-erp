<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Expense extends Model
{
    protected $fillable = [
        'expense_no',
        'category_account_id',
        'paid_from_account_id',
        'amount',
        'expense_date',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'category_account_id');
    }

    public function paidFrom(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'paid_from_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledgerTransaction(): MorphOne
    {
        return $this->morphOne(LedgerTransaction::class, 'reference');
    }
}
