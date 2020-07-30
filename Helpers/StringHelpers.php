<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Helpers;

class StringHelpers
{
    /**
     * Compare only the alpha characters (letters) in 2 strings.
     */
    public static function areSameStringsIfYouCompareOnlyLetters(string $string1, string $string2): bool
    {
        return self::prepareForLetterComparison($string1) == self::prepareForLetterComparison($string2);
    }

    /**
     * Extract only the alpha characters (letters) from a string.
     */
    private static function prepareForLetterComparison(string $string): string
    {
        $arr = str_split(strtolower(extractLetters($string)));
        sort($arr);

        return implode('', $arr);
    }
}
