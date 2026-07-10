<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CapitalTransaction extends Model
{
    public const TYPES = [
        'investment' => 'Investment',
        'drawing' => 'Drawing',
    ];

    protected $fillable = [
        'transaction_no',
        'type',
        'account_id',
        'amount',
        'transaction_date',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
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
