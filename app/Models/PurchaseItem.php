<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_cost',
        'subtotal',
        'received_quantity',
        'returned_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'received_quantity' => 'decimal:4',
        'returned_quantity' => 'decimal:4',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function remaining(): float
    {
        return round((float) $this->quantity - (float) $this->received_quantity, 4);
    }

    /**
     * How much of what's been received on this line is still eligible to
     * send back to the supplier — never more than what actually arrived.
     */
    public function returnable(): float
    {
        return round((float) $this->received_quantity - (float) $this->returned_quantity, 4);
    }
}
