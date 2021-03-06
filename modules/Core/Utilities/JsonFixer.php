<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Utilities;

use Illuminate\Support\Str;

class JsonFixer
{
    public static function attemptFix($jsonString)
    {
        if ( ! is_string($jsonString)) {
            return null;
        }

        if (is_json($jsonString)) {
            return $jsonString;
        }

        if ( ! Str::startsWith($jsonString, ['{', '['])) {
            return null;
        }

        $expl               = explode('{', $jsonString);
        $lastElement        = array_key_last($expl);
        $lastJsonStringPart = preg_replace('/[^}\]]/', '', $expl[$lastElement]);
        unset($expl[$lastElement]);

        $fixed = rtrim(implode('{', $expl), ', \t\n\r\0\x0B').$lastJsonStringPart;

        if (is_json($fixed)) {
            return $fixed;
        }

        return null;
    }
}
