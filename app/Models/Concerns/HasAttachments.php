<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * For any model that needs reusable document/file storage beyond a single
 * `*_path` column (NID copy, appointment letter, certificates, ...).
 * Add `use HasAttachments;` — no migration needed on the host model, the
 * `attachments` table already carries the polymorphic `attachable_*` keys.
 */
trait HasAttachments
{
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
