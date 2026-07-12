<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'legal_name',
        'tagline',
        'about_text',
        'mission_text',
        'vision_text',
        'values_text',
        'logo_path',
        'hero_image_path',
        'email',
        'phone',
        'address',
        'currency',
        'vat_registration_no',
        'bin_no',
        'financial_year_start_month',
        'ecommerce_enabled',
    ];

    protected $casts = [
        'ecommerce_enabled' => 'boolean',
    ];

    /**
     * There is only ever one row (id 1) — every tenant has exactly one company profile.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['name' => config('app.name', 'Business ERP')]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->hero_image_path ? Storage::disk('public')->url($this->hero_image_path) : null;
    }

    /**
     * Spoken currency name for the "amount in words" line on printed
     * vouchers — see App\Support\AmountInWords.
     */
    public function getCurrencyLabelAttribute(): string
    {
        return match ($this->currency) {
            'BDT' => 'Taka',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            default => (string) $this->currency,
        };
    }
}
