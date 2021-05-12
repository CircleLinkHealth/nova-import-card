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

        $expl        = explode('{', $jsonString);
        $lastIndex   = array_key_last($expl);
        $lastElement = $expl[$lastIndex];
        unset($expl[$lastIndex]);
        $lastJsonStringPart = '';

        $charsToTrim = ', \t\n\r\0\x0B';
        $fixed       = rtrim(implode('{', $expl), $charsToTrim);

        if (self::shouldAddClosingChar($fixed, '[', ']')) {
            $lastJsonStringPart .= ']';
        }

        if (self::shouldAddClosingChar($fixed, '{', '}')) {
            $lastJsonStringPart .= '}';
        }

        if (self::shouldAddClosingChar($lastElement, '"', '"')) {
            $lastElement .= '"';
        }

        $withLastLine = $fixed.', {'.rtrim($lastElement, $charsToTrim).'}'.$lastJsonStringPart;
        if (is_json($withLastLine)) {
            return $withLastLine;
        }

        $withoutLastLine = $fixed.$lastJsonStringPart;
        if (is_json($withoutLastLine)) {
            return $withoutLastLine;
        }

        $escaped = self::attemptToEscapeDoubleQuotes($withoutLastLine);

        if (is_json($escaped)){
            return $escaped;
        }

        return null;
    }

    private static function shouldAddClosingChar(string $string, string $openingChar, string $closingChar)
    {
        $openingCount = substr_count($string, $openingChar);

        if (0 === $openingCount) {
            return false;
        }

        $closingCount = substr_count($string, $closingChar);

        if ($openingChar === $closingChar && 0 !== $openingCount % 2) {
            return true;
        }

        return 1 === $openingCount - $closingCount;
    }

    public static function attemptToEscapeDoubleQuotes(string $string)
    {
        $exploded = explode('"', $string);

        $previousContainsAlphaNum = false;
        for ($i = 0; $i < count($exploded); $i++){
            $currentString = $exploded[$i];

            $currentStringContainsAlphaNum = self::stringContainsAlphanum($currentString);

            if (empty($currentString) || !$currentStringContainsAlphaNum){
                $previousContainsAlphaNum = false;
                continue;
            }

            if ($previousContainsAlphaNum && $currentStringContainsAlphaNum){
                $previousString = $exploded[$i-1];
                $exploded[$i -1] = $previousString.'\\';
                continue;
            }
            $previousContainsAlphaNum = $currentStringContainsAlphaNum;
        }

        $imploded = implode('"', $exploded);
        return $imploded;
    }

    public static function stringContainsAlphanum(string $string):bool{
        return  preg_match( '/[a-zA-Z]/', $string) || preg_match( '/\d/', $string );
    }
}
