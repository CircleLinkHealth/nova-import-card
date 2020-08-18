<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateValidatorMultipleFormats implements Rule
{
    private array $dateFormats;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $dateFormats)
    {
        $this->dateFormats = $dateFormats;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please enter the date in the correct format';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach ($this->dateFormats as $format) {
            $parsed = date_parse_from_format($format, $value);

            if (0 === $parsed['error_count'] && 0 === $parsed['warning_count']) {
                return true;
            }
        }

        return false;
    }
}
