<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Rules;

use Illuminate\Contracts\Validation\Rule;

class EligibilityProblems implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Problems field must contain at least 1 Problem that has a valid name or code.';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $count = collect($value)
            ->reject(function ($problem) {
                if (array_key_exists('Name', $problem)) {
                    $name = $problem['Name'] ?? null;
                    $code = $problem['Code'] ?? null;
                } else {
                    $name = $problem['name'] ?? null;
                    $code = $problem['code'] ?? null;
                }

                if (in_array(strtolower($name), ['null', 'n/a', 'none', 'n\a'])) {
                    $name = null;
                }
                if (in_array(strtolower($code), ['null', 'n/a', 'none', 'n\a'])) {
                    $code = null;
                }

                return ! $name && ! $code;
            })->count();

        return $count >= 1;
    }
}
