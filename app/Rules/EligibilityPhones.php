<?php

namespace App\Rules;

use Validator;
use Illuminate\Contracts\Validation\Rule;

class EligibilityPhones implements Rule
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

                    $validator = Validator::make(['phoneNumber' => $phone], [
                        'phoneNumber'     => 'required|phone:AUTO,US',
                    ]);

                    return $validator->fails();
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
