<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Rule;

class HasEnoughProblems implements Rule
{
    /** @var User */
    private $patient;

    /**
     * Create a new rule instance.
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Care Plan must have two CPM problems for CCM, one if practice has PCM (G2065) enabled or one BHI problem.';
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

        if ($bhiProblems >= 1) {
            return true;
        }

        // if we reach here, it means that we have no $bhiProblems.
        // so, if only one ccm problem, we check if PCM is enabled for the practice
        // otherwise, we return true only if ccm problems are equal or more than two
        if (1 === $cpmProblems) {
            return $this->patient->primaryPractice->hasServiceCode(ChargeableService::PCM);
        }

        return $cpmProblems >= 2;
    }
}
