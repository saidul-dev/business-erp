<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Copies the git-tracked default banner (database/seeders/assets) into
 * public storage and sets it as the homepage hero photo (see
 * website/home.blade.php) — so a fresh install already has a real photo
 * hero instead of an empty gradient. storage/app/public is gitignored (only
 * uploaded content lives there), so the source file has to live outside it
 * to survive a fresh clone.
 */
class HeroImageSeeder extends Seeder
{
    protected const SOURCE = __DIR__.'/assets/default-hero.png';

    protected const TARGET_PATH = 'company/default-hero.png';

    public function run(?int $tenantId = null): void
    {
        $company = CompanySetting::current($tenantId);

        // Never overwrite a photo a client already uploaded.
        if ($company->hero_image_path) {
            return;
        }

        Storage::disk('public')->put(self::TARGET_PATH, file_get_contents(self::SOURCE));
        $company->update(['hero_image_path' => self::TARGET_PATH]);
    }
}
