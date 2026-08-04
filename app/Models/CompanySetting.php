<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
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
     * Exactly one row per Tenant. Defaults to the authenticated user's own
     * tenant — pass `$tenantId` explicitly from contexts with no logged-in
     * user (a seeder provisioning a brand-new tenant's defaults). With no
     * tenant either way (an anonymous visitor on a page that still renders
     * <x-website-layout>), returns an unsaved instance with just the app
     * name — nothing to persist without a tenant_id to attach it to.
     */
    public static function current(?int $tenantId = null): self
    {
        $tenantId ??= auth()->user()?->tenant_id;

        if ($tenantId === null) {
            return new static(['name' => config('app.name', 'Business ERP')]);
        }

        return static::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->firstOrCreate(['tenant_id' => $tenantId], ['name' => config('app.name', 'Business ERP')]);
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
