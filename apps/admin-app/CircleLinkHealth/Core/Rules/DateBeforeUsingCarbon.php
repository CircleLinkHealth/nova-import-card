<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class DateBeforeUsingCarbon implements Rule
{
    /**
     * For some cases, mysql can handle only 4 digit years.
     * For example, date '23456-01-01' would result to '0000-00-00'.
     */
    const MAX_DATE_DEFAULT = '9999-12-31';

    /**
     * @var Carbon
     */
    private $MAX_DATE;

    /**
     * Create a new rule instance.
     *
     * @param $maxDate
     */
    public function __construct($maxDate = null)
    {
        $this->MAX_DATE = Carbon::parse($maxDate ?? DateBeforeUsingCarbon::MAX_DATE_DEFAULT);
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
     * @param mixed  $value
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
