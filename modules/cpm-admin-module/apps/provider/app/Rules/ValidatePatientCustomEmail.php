<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class ValidatePatientCustomEmail implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
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
        return 'Email is invalid.';
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
        // invalid email
        if (empty($value)) {
            return false;
        }

        if ( ! Str::contains($value, '@')) {
            return false;
        }

        if (Str::contains($value, ['@careplanmanager.com', '@example.com', '@noEmail.com'])) {
            return false;
        }

        return true;
    }
}
