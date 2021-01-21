<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Rules;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\FiresImportingHooks;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use Illuminate\Contracts\Validation\Rule;

class MrnWasReplacedIfPracticeImportingHooks implements Rule
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
        $route = url('/superadmin/resources/supplemental-patient-data-resources');

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
        if ( ! FiresImportingHooks::hasListener(
            ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO,
            ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME,
            $this->patientUser->primaryPractice
        )) {
            return true;
        }

        $dataFromPractice = SupplementalPatientData::where('first_name', 'like', "{$this->patientUser->first_name}%")
            ->where('last_name', $this->patientUser->last_name)
            ->where('practice_id', $this->patientUser->primaryPractice->id)
            ->where('dob', $this->patientUser->getBirthDate())
            ->first();

        if ( ! $dataFromPractice) {
            return false;
        }

        return $this->patientUser->getMRN() == $dataFromPractice->mrn;
    }
}
