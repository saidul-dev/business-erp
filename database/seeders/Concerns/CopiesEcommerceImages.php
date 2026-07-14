<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Demo product/category/banner photos ship git-tracked under
 * public/assets/ecommerce (survives a fresh clone), but every image_path
 * column in this app is resolved via Storage::disk('public')->url() —
 * i.e. relative to storage/app/public, which is gitignored. So seeding
 * copies each source photo into the public disk once and stores that
 * disk-relative path, the same way HeroImageSeeder seeds the company's
 * default hero photo.
 */
trait CopiesEcommerceImages
{
    /**
     * @param  string  $source  Path relative to public/assets/ecommerce
     * @param  string  $target  Path relative to the "public" storage disk
     * @return string  $target, for convenient assignment to an *_path column
     */
    protected function copyEcommerceImage(string $source, string $target): string
    {
        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put(
                $target,
                file_get_contents(public_path('assets/ecommerce/'.$source))
            );
        }

        return $target;
    }
}
