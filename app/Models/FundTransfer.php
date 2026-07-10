<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class FundTransfer extends Model
{
    protected $fillable = [
        'transfer_no',
        'from_account_id',
        'to_account_id',
        'amount',
        'transfer_date',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'to_account_id');
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
