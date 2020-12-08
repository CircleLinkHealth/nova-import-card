<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Helpers;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporter;

class StringHelpers
{
    /**
     * Compare only the alpha characters (letters) in 2 strings.
     */
    public static function areSameStringsIfYouCompareOnlyLetters(string $string1, string $string2): bool
    {
        return self::prepareForLetterComparison($string1) == self::prepareForLetterComparison($string2);
    }

    public static function partialOrFullNameMatch(string $name1, string $name2): bool
    {
        return StringHelpers::areSameStringsIfYouCompareOnlyLetters($name1, $name2)
            || CcdaImporter::isSameNameButOneHasMiddleInitial($name1, $name2)
            || StringHelpers::areSameStringsIfYouCompareOnlyLetters(StringHelpers::removeNameSuffix($name1), StringHelpers::removeNameSuffix($name2));
    }

    public static function removeNameSuffix(string $name)
    {
        return str_replace([
            ' sr',
            ' jr',
        ], '', strtolower($name));
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
