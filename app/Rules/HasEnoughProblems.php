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

    /** @var User $patient */
    private $patient;

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

        if ($cpmProblems === 1) {
            return $this->patient->primaryPractice->hasServiceCode(ChargeableService::PCM);
        }

        return $cpmProblems >= 2 || $bhiProblems >= 1;
    }
}
