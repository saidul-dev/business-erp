<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'selling_price', 'estimated_cost', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'selling_price' => 'decimal:2',
        // 4dp — see Product::$casts for why.
        'estimated_cost' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_values')
            ->withPivot('attribute_id');
    }

    /**
     * Human-readable variant label, e.g. "Red / M".
     */
    public function getLabelAttribute(): string
    {
        return $this->attributeValues->pluck('value')->join(' / ');
    }
}
