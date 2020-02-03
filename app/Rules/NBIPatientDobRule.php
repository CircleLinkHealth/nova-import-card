<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class NBIPatientDobRule implements Rule
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
        try {
            $date = Carbon::parse($value);
        } catch (\Exception $exception){
            return false;
        }

        $now = Carbon::now();

        if ($date->gt($now->copy()->subYear(10))){
            return false;
        }

        if ($date->lt($now->copy()->subYear(100))){
            return false;
        }
        //strip value and $date->toDateString. All value integers should exist in the to date string

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        //customize message
        return 'The validation error message.';
    }
}
