<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'legal_name',
        'logo_path',
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
}
