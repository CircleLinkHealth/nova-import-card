<?php

namespace App\CLH\Helpers;


class StringManipulation
{
    /**
     * Returns only numerical values in a string
     *
     * @param $string
     * @return string
     */
    public function extractNumbers($string)
    {
        preg_match_all('/([\d]+)/', $string, $match);

        return implode($match[0]);
    }


    /**
     * Formats a string of numbers as a phone number delimited by dashes as such: xxx-xxx-xxxx
     * @param $string
     * @return string
     */
    public function formatPhoneNumber($string)
    {
        $sanitized = $this->extractNumbers($string);

        if (strlen($sanitized) > 10) {
            $sanitized = substr($sanitized, -10);
        }

        return substr($sanitized, 0, 3) . '-' . substr($sanitized, 3, 3) . '-' . substr($sanitized, 6, 4);
    }


}