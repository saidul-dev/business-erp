<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A stripped-down, single-product promo page for ad traffic (see
 * CampaignPageController) — reachable at /campaign/{slug}, deliberately
 * outside the normal storefront layout/navigation.
 */
class CampaignPage extends Model
{
    protected $fillable = [
        'product_id',
        'slug',
        'headline',
        'subheadline',
        'banner_image_path',
        'features',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Falls back to the product's own image when no custom banner was
     * uploaded — the admin form pre-fills from the product but an override
     * is always optional.
     */
    public function getBannerImageUrlAttribute(): ?string
    {
        if ($this->banner_image_path) {
            return Storage::disk('public')->url($this->banner_image_path);
        }

        return $this->product?->image_url;
    }

    /**
     * One feature bullet per line — blank lines dropped.
     */
    public function getFeatureListAttribute(): array
    {
        return collect(explode("\n", (string) $this->features))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
