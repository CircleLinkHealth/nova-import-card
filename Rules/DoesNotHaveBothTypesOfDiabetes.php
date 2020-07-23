<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Rules;

use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Contracts\Validation\Rule;

class DoesNotHaveBothTypesOfDiabetes implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
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
        return 'The Care Plan has both Diabetes Type 1 and Diabetes Type 2. Please confirm that this is accurate by clicking "Approve" and confirm conditions in the modal, or choose the correct type of Diabetes for the patient. ';
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
        //if both Diabetes types are present fail
        if ($value->firstWhere('cpmProblem.name', CpmProblem::DIABETES_TYPE_1) && $value->firstWhere('cpmProblem.name', CpmProblem::DIABETES_TYPE_2)) {
            return false;
        }

        return true;
    }
}
