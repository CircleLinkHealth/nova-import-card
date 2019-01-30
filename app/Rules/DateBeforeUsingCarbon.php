<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class DateBeforeUsingCarbon implements Rule
{

    private $MAX_DATE;

    /**
     * Create a new rule instance.
     *
     * @param $maxDate
     */
    public function __construct($maxDate)
    {
        $this->MAX_DATE = Carbon::parse($maxDate);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Date must be less than {$this->MAX_DATE->toDateString()}";
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $val = Carbon::createFromFormat('Y-m-d', $value);

            return $val->lessThan($this->MAX_DATE);
        } catch (\Exception $exception) {
            return false;
        }

    }
}
