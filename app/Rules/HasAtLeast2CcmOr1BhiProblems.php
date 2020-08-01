<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * DEPRECATED.
 *
 * Replaced with {@link HasEnoughProblems}
 *
 * Class HasAtLeast2CcmOr1BhiProblems
 */
class HasAtLeast2CcmOr1BhiProblems implements Rule
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
        return 'The Care Plan must have two CPM problems, or one BHI problem.';
    }

    /**
     * Determine if the validation rule passes.
     * $value is a collection of ccdProblems.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value->isEmpty()) {
            return false;
        }

        $cpmProblems = $value->count();
        $bhiProblems = $value->where('cpmProblem.is_behavioral', true)->count();

        return $cpmProblems >= 2 || $bhiProblems >= 1;
    }
}
