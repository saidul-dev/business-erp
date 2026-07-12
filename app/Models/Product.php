<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'stock_unit_id',
        'purchase_unit_id',
        'purchase_unit_conversion',
        'sale_unit_id',
        'sale_unit_conversion',
        'description',
        'estimated_cost',
        'selling_price',
        'compare_at_price',
        'is_featured',
        'is_flash_sale',
        'reorder_level',
        'image_path',
        'status',
        'has_variants',
        'track_batch',
        'track_expiry',
        'track_serial',
        'warranty_period',
        'warranty_unit',
        'guarantee_period',
        'guarantee_unit',
    ];

    protected $casts = [
        'status' => 'boolean',
        // 4dp: a perpetual weighted-average, recalculated on every costed "in"
        // movement (see StockMovement::recalculateAverageCost()) — rounding
        // it to money precision here would drift qty × cost off the ledger's
        // true total. UI display still formats to 2dp.
        'estimated_cost' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_flash_sale' => 'boolean',
        'purchase_unit_conversion' => 'decimal:4',
        'sale_unit_conversion' => 'decimal:4',
        'reorder_level' => 'integer',
        'has_variants' => 'boolean',
        'track_batch' => 'boolean',
        'track_expiry' => 'boolean',
        'track_serial' => 'boolean',
        'warranty_period' => 'integer',
        'guarantee_period' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * The unit stock quantity is tracked in — the canonical unit.
     */
    public function stockUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'stock_unit_id');
    }

    /**
     * Unit used when purchasing this product. Falls back to the stock
     * unit (1:1) when not set — see purchase_unit_conversion.
     */
    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    /**
     * Unit used when selling this product. Falls back to the stock
     * unit (1:1) when not set — see sale_unit_conversion.
     */
    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isVariable(): bool
    {
        return (bool) $this->has_variants;
    }

    /**
     * Whole-number "% off" for the storefront's discount badge, or null
     * when there's nothing to show (no compare_at_price, or it's not
     * actually higher than the selling price).
     */
    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->compare_at_price || (float) $this->compare_at_price <= (float) $this->selling_price) {
            return null;
        }

        return (int) round((1 - (float) $this->selling_price / (float) $this->compare_at_price) * 100);
    }
}
