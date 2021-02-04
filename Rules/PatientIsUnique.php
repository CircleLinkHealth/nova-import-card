<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Rules;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use Illuminate\Contracts\Validation\Rule;

class PatientIsUnique implements Rule
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

    public function getPatientUserId(): ?int
    {
        return $this->duplicatePatientUserId;
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
        $this->duplicatePatientUserId = $this->mysqlMatchPatient();

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

    private function mysqlMatchPatient(): ?int
    {
        return User::whereRaw("MATCH(display_name, first_name, last_name) AGAINST('+$this->firstName +$this->lastName' IN BOOLEAN MODE)")
            ->ofPractice($this->practiceId)
            ->whereHas(
                'patientInfo',
                function ($q) {
                    $q->where('birth_date', $this->dob);
                }
            )
            ->when($this->patientUserId, function ($q) {
                $q->where('id', '!=', $this->patientUserId);
            })
            ->where(function ($q) {
                $q->ofType(['participant', 'survey-only'])
                    ->orWhereDoesntHave('roles');
            })
            ->value('id');
    }
}
