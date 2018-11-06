<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CsvEligibilityProblems implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $count = collect($value)
            ->reject(function ($problem) {
                $name = $problem['Name'] ?? null;
                $code = $problem['Code'] ?? null;
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

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Problems field must contain at least 1 Problem that has a valid name or code.';
    }
}
