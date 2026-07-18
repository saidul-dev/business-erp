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
        'default_shift_start_time',
        'late_grace_minutes',
    ];

    protected $casts = [
        'late_grace_minutes' => 'integer',
    ];

    /**
     * There is only ever one row (id 1) — every tenant has exactly one company profile.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['name' => config('app.name', 'Business ERP')]);
    }

    /**
     * The instant, on the given date, after which a check-in counts as
     * late — shift start plus the configured grace period. Shared by both
     * self check-in and HR's manual attendance register so "late" means
     * the same thing regardless of who recorded the time.
     */
    public function lateThresholdFor(\Carbon\Carbon $date): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($date->format('Y-m-d').' '.$this->default_shift_start_time)
            ->addMinutes($this->late_grace_minutes);
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
