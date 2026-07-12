<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A slide in the e-commerce homepage's hero carousel — only rendered when
 * CompanySetting::ecommerce_enabled is on. Order is purely `sort_order`,
 * managed from Admin > Settings > Hero Slides.
 */
class HeroSlide extends Model
{
    protected $fillable = [
        'image_path',
        'link_url',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
