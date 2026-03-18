<?php

namespace App\Helpers;

class NumberToWords
{
    private static array $units = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf',
    ];

    private static array $tens = [
        '', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt',
    ];

    public static function convert(float $number): string
    {
        $intPart = (int) floor(abs($number));
        $decPart = round((abs($number) - $intPart) * 100);

        $result = self::intToWords($intPart);

        if ($intPart === 0) {
            $result = 'zéro';
        }

        $result .= $intPart > 1 ? ' dirhams' : ' dirham';

        if ($decPart > 0) {
            $result .= ' et ' . self::intToWords((int) $decPart) . ' centimes';
        }

        return ucfirst($result);
    }

    private static function intToWords(int $n): string
    {
        if ($n === 0) return '';
        if ($n < 20) return self::$units[$n];

        if ($n < 100) return self::twoDigits($n);

        if ($n < 1000) {
            $h = (int) ($n / 100);
            $rest = $n % 100;
            $word = $h === 1 ? 'cent' : self::$units[$h] . ' cent';
            if ($rest === 0 && $h > 1) $word .= 's';
            if ($rest > 0) $word .= ' ' . self::intToWords($rest);
            return $word;
        }

        if ($n < 1000000) {
            $t = (int) ($n / 1000);
            $rest = $n % 1000;
            $word = $t === 1 ? 'mille' : self::intToWords($t) . ' mille';
            if ($rest > 0) $word .= ' ' . self::intToWords($rest);
            return $word;
        }

        if ($n < 1000000000) {
            $m = (int) ($n / 1000000);
            $rest = $n % 1000000;
            $word = self::intToWords($m) . ($m > 1 ? ' millions' : ' million');
            if ($rest > 0) $word .= ' ' . self::intToWords($rest);
            return $word;
        }

        return (string) $n;
    }

    private static function twoDigits(int $n): string
    {
        $t = (int) ($n / 10);
        $u = $n % 10;

        // 70-79: soixante-dix...
        if ($t === 7) {
            return 'soixante-' . self::$units[10 + $u];
        }

        // 90-99: quatre-vingt-dix...
        if ($t === 9) {
            return 'quatre-vingt-' . self::$units[10 + $u];
        }

        $word = self::$tens[$t];

        // 80 exactly: quatre-vingts
        if ($t === 8 && $u === 0) return 'quatre-vingts';

        if ($u === 0) return $word;

        // "et un" for 21, 31, 41, 51, 61
        if ($u === 1 && $t >= 2 && $t <= 6) {
            return $word . ' et un';
        }

        return $word . '-' . self::$units[$u];
    }
}
