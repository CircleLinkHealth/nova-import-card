<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class DateEqualOrAfterUsingCarbon implements Rule
{
    /**
     * @var Carbon
     */
    private $minDate;

    /**
     * Create a new rule instance.
     *
     * @param $maxDate
     * @param mixed|null $minDate
     */
    public function __construct($minDate = null)
    {
        $this->minDate = $minDate ? Carbon::parse($minDate) : Carbon::today();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Date must be equal or more than {$this->minDate->toDateString()}";
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

            return $val->gte($this->minDate);
        } catch (\Exception $exception) {
            return false;
        }
    }
}
