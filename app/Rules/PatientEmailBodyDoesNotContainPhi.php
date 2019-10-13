<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use App\Constants;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Rule;

class PatientEmailBodyDoesNotContainPhi implements Rule
{
    private $patientUser;

    private $phiFound = [];

    /**
     * Create a new rule instance.
     *
     * @param User $patientUser
     */
    public function __construct(User $patientUser)
    {
        $this->patientUser = $patientUser;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $phiFieldsString = implode(', ', $this->phiFound);

        return 'Email body contains patient PHI: '.$phiFieldsString;
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
        $this->patientUser->loadMissing(Constants::PATIENT_PHI_RELATIONSHIPS);

        //check if string contains and just add fields that are found. Looping over each one individually, so we can report back to the user which phi exist in the message

        $value = strtolower($value);

        //For User
        foreach ($this->patientUser->phi as $phi) {
            $this->phiFound[] = str_contains($value, strtolower($this->patientUser->getAttribute($phi))) ? $phi : null;
        }

        //For Relationships
        foreach ($this->patientUser->getRelations() as $relation) {
            foreach ($relation->phi as $phi) {
                $this->phiFound[] = str_contains($value, strtolower($relation->getAttribute($phi))) ? $phi : null;
            }
        }

        $this->phiFound = array_filter($this->phiFound);

        return empty($this->phiFound);
    }
}
