<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class JsonEligibilityPhones implements Rule
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
            ->reject(function ($phone) {
                if (! preg_match("/\A[(]?[0-9]{3}[)]?[ ,-]?[0-9]{3}[ ,-]?[0-9]{4}\z/", $phone)) {
                    $phone = null;
                }
                return ! $phone;
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
        return 'Must contain at least 1 valid US phone number.';
    }
}
