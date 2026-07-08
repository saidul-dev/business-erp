<?php

namespace App\Models\Concerns;

use App\Models\Scopes\SiteScope;
use App\Models\Site;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * For every site-aware model (Purchase, Sale, Inventory, Payroll, Voucher...
 * — see the ERP Architecture Guideline v1.0). Add a `site_id` foreign key
 * to the model's migration, `use BelongsToSite`, and:
 *   - reads are auto-filtered to the current user's Current Site
 *   - `site_id` is auto-filled from the current user's Current Site on create
 * No manual `where('site_id', ...)` needed anywhere else in the app.
 */
trait BelongsToSite
{
    protected static function bootBelongsToSite(): void
    {
        static::addGlobalScope(new SiteScope);

        static::creating(function ($model) {
            if (! $model->site_id && auth()->check()) {
                $model->site_id = auth()->user()->current_site_id;
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
