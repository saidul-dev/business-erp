<?php

namespace App\Support;

/**
 * Spells out a decimal amount for the "in words" line on a printed
 * financial voucher (Payment / Collection) — e.g. 5596.67 becomes
 * "Five Thousand Five Hundred Ninety Six and 67/100". Standard
 * international (thousand/million) grouping, matching the
 * number_format() grouping already used everywhere else in the app.
 */
class AmountInWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    private const SCALES = ['', ' Thousand', ' Million', ' Billion'];

    public static function convert(float $amount, string $currencyLabel = 'Taka'): string
    {
        $amount = round(abs($amount), 2);
        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);

        $words = $whole === 0 ? 'Zero' : self::spellInteger($whole);

        return trim("{$currencyLabel} {$words} and {$fraction}/100 Only");
    }

    private static function spellInteger(int $number): string
    {
        if ($number === 0) {
            return '';
        }

        $groups = [];
        while ($number > 0) {
            $groups[] = $number % 1000;
            $number = intdiv($number, 1000);
        }

        $parts = [];
        foreach (array_reverse($groups, true) as $index => $group) {
            if ($group === 0) {
                continue;
            }
            $parts[] = self::spellHundreds($group).self::SCALES[$index];
        }

        return trim(implode(' ', $parts));
    }

    private static function spellHundreds(int $number): string
    {
        $parts = [];

        if ($number >= 100) {
            $parts[] = self::ONES[intdiv($number, 100)].' Hundred';
            $number %= 100;
        }

        if ($number >= 20) {
            $tens = self::TENS[intdiv($number, 10)];
            $ones = self::ONES[$number % 10];
            $parts[] = trim($tens.' '.$ones);
        } elseif ($number > 0) {
            $parts[] = self::ONES[$number];
        }

        return implode(' ', $parts);
    }
}
