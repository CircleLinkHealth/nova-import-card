<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core;

class StringManipulation
{
    /**
     * Returns only decimal numbers (numerical values and dots) in a string.
     *
     * @param $string
     *
     * @return string
     */
    public function extractDecimalNumbers($string)
    {
        preg_match_all('/[0-9]*\.?[0-9]+/', $string, $match);

        return implode($match[0]);
    }

    /**
     * Returns only numerical values in a string.
     *
     * @param $string
     *
     * @return string
     */
    public function extractNumbers($string)
    {
        preg_match_all('/([\d]+)/', $string, $match);

        return implode($match[0]);
    }

    /**
     * Formats a string of numbers as a phone number delimited by dashes as such: xxx-xxx-xxxx.
     *
     * @param $string
     *
     * @return string
     */
    public function formatPhoneNumber($string)
    {
        $sanitized = $this->sanitizeNumber($string);

        if (is_null($sanitized)) {
            return '';
        }

        return substr($sanitized, 0, 3).'-'.substr($sanitized, 3, 3).'-'.substr($sanitized, 6, 4);
    }

    /**
     * Formats a phone number in E164 format, eg. +12223334444.
     *
     * @param $string
     * @param string $countryCode
     *
     * @return bool|string
     */
    public function formatPhoneNumberE164(
        $string,
        $countryCode = '1'
    ) {
        $sanitized = $this->sanitizeNumber($string);

        if (is_null($sanitized)) {
            return '';
        }

        return "+$countryCode$sanitized";
    }

    /**
     * Formats a string of numbers as a phone number delimited by parenthesis and dashes as such: (xxx) xxx-xxxx.
     * NPA-NXX-XXXX.
     */
    public function formatPhoneNumberWithNpaParenthesized(?string $string): string
    {
        $sanitized = $this->sanitizeNumber($string);

        if (is_null($sanitized)) {
            return '';
        }

        return '('.substr($sanitized, 0, 3).')'.' '.substr($sanitized, 3, 3).'-'.substr($sanitized, 6, 4);
    }

    /**
     * Returns the differences between two strings.
     *
     * Used for importing Aprima CCD Records.
     *
     * Example:
     * stringDiff('Diovan 160 mg tablet', 'Diovan 160 mg tabletDiovan 160 mg tablet, 1 Tablet(s) PO every day');
     * returns: ', 1 Tablet(s) PO every day'
     *
     * @param $needle
     * @param $haystack
     *
     * @return string
     */
    public function stringDiff(
        $needle,
        $haystack
    ) {
        return str_replace($needle, '', $haystack);
    }

    private function sanitizeNumber(?string $string): ?string
    {
        $sanitized = $this->extractNumbers($string);

        if (strlen($sanitized) < 10) {
            return null;
        }

        if (strlen($sanitized) > 10) {
            $sanitized = substr($sanitized, -10);
        }

        return $sanitized;
    }
}
