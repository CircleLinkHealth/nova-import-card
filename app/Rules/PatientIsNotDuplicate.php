<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use Illuminate\Contracts\Validation\Rule;

class PatientIsNotDuplicate implements Rule
{
    /**
     * @var string
     */
    protected $dob;
    /**
     * @var string
     */
    protected $firstName;
    /**
     * @var string
     */
    protected $lastName;
    /**
     * @var string
     */
    protected $mrn;
    /**
     * @var int
     */
    protected $practiceId;
    /**
     * @var int
     */
    private $duplicatePatientUserId;
    /**
     * @var int|null
     */
    private $patientUserId;

    /**
     * Create a new rule instance.
     *
     * @param string $mrn
     *
     * @throws \Exception
     */
    public function __construct(int $practiceId, string $firstName, string $lastName, string $dob, $mrn = null, int $patientUserId = null)
    {
        $this->practiceId    = $practiceId;
        $this->firstName     = $firstName;
        $this->lastName      = $lastName;
        $this->mrn           = $mrn;
        $this->dob           = ImportPatientInfo::parseDOBDate($dob);
        $this->patientUserId = $patientUserId;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This patient is a duplicate of patient with ID '.$this->duplicatePatientUserId;
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
        $this->duplicatePatientUserId = User::whereFirstName($this->firstName)
            ->whereLastName($this->lastName)
            ->whereHas(
                'patientInfo',
                function ($q) {
                                                    $q->where('birth_date', $this->dob);
                                                }
            )->where('program_id', $this->practiceId)
            ->when($this->patientUserId, function ($q) {
                $q->where('id', '!=', $this->patientUserId);
            })
            ->value('id');

        if ($this->duplicatePatientUserId) {
            return false;
        }

        if ($this->mrn) {
            $this->duplicatePatientUserId = Patient::whereHas(
                'user',
                function ($q) {
                    $q->where('program_id', $this->practiceId);
                }
            )->whereMrnNumber($this->mrn)->whereNotNull('mrn_number')
                ->when($this->patientUserId, function ($q) {
                    $q->where('user_id', '!=', $this->patientUserId);
                })
                ->value('user_id');

            if ($this->duplicatePatientUserId) {
                return false;
            }
        }

        return true;
    }
}
