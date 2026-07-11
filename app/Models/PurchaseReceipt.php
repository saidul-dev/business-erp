<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PurchaseReceipt extends Model
{
    protected $fillable = [
        'purchase_id',
        'receipt_no',
        'received_date',
        'note',
        'received_by',
        'delivery_charge',
        'other_charge',
        'charge_paid_via',
        'charge_account_id',
    ];

    protected $casts = [
        'received_date' => 'date',
        'delivery_charge' => 'decimal:2',
        'other_charge' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function chargeAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'charge_account_id');
    }

    public function landedCost(): float
    {
        return round((float) $this->delivery_charge + (float) $this->other_charge, 2);
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function ledgerTransaction(): MorphOne
    {
        return $this->morphOne(LedgerTransaction::class, 'reference');
    }
}
