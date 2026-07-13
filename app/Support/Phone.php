<?php

namespace App\Support;

/**
 * Normalizes a Bangladeshi mobile number to its canonical local form
 * (01XXXXXXXXX) so the same guest always matches to one Party record on
 * checkout and can look their order back up later, regardless of how they
 * typed it (+880, 880, spaces, dashes, etc) — nothing in the app did this
 * before, every phone field elsewhere is still a raw unnormalized string.
 */
class Phone
{
    public static function normalize(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);

        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = '0'.substr($digits, 3);
        }

        return $digits;
    }

    public static function isValidBangladeshiMobile(string $normalized): bool
    {
        return (bool) preg_match('/^01[3-9]\d{8}$/', $normalized);
    }
}
