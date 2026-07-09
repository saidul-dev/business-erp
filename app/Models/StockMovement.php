<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    /**
     * Fixed movement types and their ledger direction. Direction is always
     * derived from this map (see booted()) — never set directly — so a row
     * can never disagree with its own type.
     */
    public const TYPES = [
        'initial_stock' => 'in',
        'purchase' => 'in',
        'production' => 'in',
        'transfer_in' => 'in',
        'adjustment_in' => 'in',
        'sales_return' => 'in',
        'sale' => 'out',
        'transfer_out' => 'out',
        'adjustment_out' => 'out',
        'purchase_return' => 'out',
        'production_consumption' => 'out',
        'damage_expiry' => 'out',
        'sample' => 'out',
        'internal_consumption' => 'out',
    ];

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'site_id',
        'type',
        'quantity',
        'unit_cost',
        'batch_no',
        'expiry_date',
        'serial_no',
        'note',
        'moved_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'expiry_date' => 'date',
        'moved_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement) {
            $movement->direction = self::TYPES[$movement->type]
                ?? throw new \InvalidArgumentException("Unknown stock movement type [{$movement->type}].");
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
