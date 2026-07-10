<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Income extends Model
{
    protected $fillable = [
        'income_no',
        'category_account_id',
        'received_into_account_id',
        'amount',
        'income_date',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'category_account_id');
    }

    public function receivedInto(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'received_into_account_id');
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
