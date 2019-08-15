<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use App\Importer\CarePlanHelper;
use App\Models\PatientData\NBI\PatientData;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Rule;

class HasValidNbiMrn implements Rule
{
    /**
     * @var User
     * */
    protected $patientUser;

    /**
     * Create a new rule instance.
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
        $route = url('/superadmin/resources/n-b-i-patient-datas');

        return "The MRN for this Patient does not correspond to the NBI List. Please visit the NBI Supplementary Data page <a href='{$route}' target=_blank><strong>here</strong></a>.";
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
        if (CarePlanHelper::NBI_PRACTICE_NAME !== $this->patientUser->primaryPractice->name) {
            return true;
        }
        $dataFromPractice = PatientData::where('first_name', 'like', "{$this->patientUser->first_name}%")
            ->where('last_name', $this->patientUser->last_name)
            ->where('dob', $this->patientUser->getBirthDate())
            ->first();
        if ( ! $dataFromPractice) {
            return false;
        }

        return $this->patientUser->getMRN() == $dataFromPractice->mrn;
    }
}
