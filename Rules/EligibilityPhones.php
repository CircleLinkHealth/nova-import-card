<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Rules;

use Illuminate\Contracts\Validation\Rule;
use Validator;

class EligibilityPhones implements Rule
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
        return 'Must contain at least 1 valid US phone number.';
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
            ->filter()
            ->reject(function ($phone) {
                $validator = Validator::make(['phoneNumber' => $phone], [
                    'phoneNumber' => 'required|phone:AUTO,US',
                ]);

                if ( ! isProductionEnv()) {
                    return false;
                }

                return $validator->fails();
            })->count();

        return $count >= 1;
    }
}
