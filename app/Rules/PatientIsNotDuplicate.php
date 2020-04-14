<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use Illuminate\Contracts\Validation\Rule;

class PatientIsNotDuplicate implements Rule
{
    /**
     * @var int
     */
    protected $practiceId;
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
     * @var string
     */
    protected $dob;
    /**
     * @var int
     */
    private $duplicatePatientUserId;
    
    /**
     * Create a new rule instance.
     *
     * @param int $practiceId
     * @param string $firstName
     * @param string $lastName
     * @param string $dob
     * @param string $mrn
     *
     * @throws \Exception
     */
    public function __construct(int $practiceId, string $firstName, string $lastName, string $dob, $mrn = null)
    {
        $this->practiceId = $practiceId;
        $this->firstName  = $firstName;
        $this->lastName   = $lastName;
        $this->mrn        = $mrn;
        $this->dob        = ImportPatientInfo::parseDOBDate($dob);
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
     * @param mixed $value
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
                                            )->where('program_id', $this->practiceId)->value('id');
        
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
                                                   ->value('user_id');
            
            if ($this->duplicatePatientUserId) {
                return false;
            }
        }
        
        return true;
    }
}
