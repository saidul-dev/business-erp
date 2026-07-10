<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'subtotal',
        'delivered_quantity',
        'returned_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'delivered_quantity' => 'decimal:4',
        'returned_quantity' => 'decimal:4',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(SaleDeliveryItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function remaining(): float
    {
        return round((float) $this->quantity - (float) $this->delivered_quantity, 4);
    }

    /**
     * How much of what's been delivered on this line is still eligible
     * to accept back from the customer — never more than what actually
     * shipped.
     */
    public function returnable(): float
    {
        return round((float) $this->delivered_quantity - (float) $this->returned_quantity, 4);
    }
}
