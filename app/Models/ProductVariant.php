<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'selling_price', 'estimated_cost', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'selling_price' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
