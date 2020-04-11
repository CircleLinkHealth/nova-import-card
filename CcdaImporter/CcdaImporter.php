<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use App\Events\PatientUserCreated;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachBillingProvider;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachDefaultPatientContactWindows;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachLocation;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachPractice;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\FirstOrCreateCarePlan;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportAllergies;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportInsurances;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportMedications;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPhones;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportProblems;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportVitals;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class CcdaImporter
{
    /**
     * @var Ccda
     */
    protected $ccda;
    /**
     * @var User
     */
    protected $patient;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\Enrollee
     */
    protected $enrollee;
    /**
     * @var StringManipulation
     */
    protected $str;
    /**
     * @var CarePlan|\Illuminate\Database\Eloquent\Model
     */
    protected $carePlan;
    
    public function __construct(
        Ccda $ccda,
        User $patient
    ) {
        $this->str   = new StringManipulation();
        $this->ccda = $ccda;
        $this->patient = $patient;
    }
    
    /**
     * Create a new CarePlan.
     *
     * @return $this
     */
    public function createNewCarePlan()
    {
        $this->carePlan = FirstOrCreateCarePlan::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    public function handleEnrollees()
    {
        $enrollee = Enrollee::duplicates($this->patient, $this->ccda)->first();
        
        if ($enrollee) {
            if (strtolower($this->patient->first_name) != strtolower($enrollee->first_name) || strtolower($this->patient->last_name) != strtolower($enrollee->last_name)) {
                throw new \Exception("Something fishy is going on. enrollee:{$enrollee->id} has user:{$enrollee->user_id}, which does not matched with user:{$this->patient->id}");
            }
            $this->enrollee        = $enrollee;
            $enrollee->user_id     = $this->patient->id;
            $enrollee->save();
        }
        
        return $this;
    }
    
    /**
     * Store AllergyImports as Allergy Models.
     *
     * @return $this
     */
    public function storeAllergies()
    {
        ImportAllergies::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Billing Provider.
     *
     * @return $this
     */
    public function storeBillingProvider()
    {
        AttachBillingProvider::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Contact Windows.
     *
     * @return $this
     */
    public function storeContactWindows()
    {
        AttachDefaultPatientContactWindows::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    public function storeImportedValues()
    {
        $this->patient->loadMissing(['primaryPractice', 'patientInfo']);
        $this->ccda->loadMissing(['location']);
        
        $this->handleEnrollees()
             ->updateTrainingFeatures();
        
        $this->createNewCarePlan()
             ->storeAllergies()
             ->storeProblemsList()
             ->storeMedications()
             ->storeBillingProvider()
             ->storeLocation()
             ->storePractice()
             ->storePatientInfo()
             ->storeContactWindows()
             ->storePhones()
             ->storeInsurance()
             ->storeVitals();
        
        // Populate display_name on User
        $this->patient->display_name = "{$this->patient->first_name} {$this->patient->last_name}";
        $this->patient->program_id   = $this->imr->practice_id ?? null;
        $this->patient->save();
    
        //This CarePlan is now ready to be QA'ed by a CLH Admin
        $this->ccda->status = Ccda::QA;
        $this->ccda->save();
        
        event(new PatientUserCreated($this->patient));
        
        return $this->carePlan;
    }
    
    /**
     * Stores Insurance.
     *
     * @return $this
     */
    public function storeInsurance()
    {
        ImportInsurances::for($this->patient, $this->ccda);
    
        return $this;
    }
    
    /**
     * Store Location.
     *
     * @return $this
     */
    public function storeLocation()
    {
        AttachLocation::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Stores MedicationImports as Medication Models.
     *
     * @return $this
     */
    public function storeMedications()
    {
        ImportMedications::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Patient Info.
     *
     * @return $this
     */
    public function storePatientInfo()
    {
        ImportPatientInfo::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Phone Numbers.
     *
     * @return $this
     */
    public function storePhones()
    {
        ImportPhones::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    public function storePractice()
    {
        AttachPractice::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store ProblemImports as Problem Models.
     *
     * @return $this
     */
    public function storeProblemsList()
    {
        ImportProblems::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Vitals.
     *
     * @todo: This only applies to CCDAs. Find a cleaner solution. This doesn't fit here.
     *
     * @return $this
     */
    public function storeVitals()
    {
        ImportVitals::for($this->patient, $this->ccda);
        
        return $this;
    }
}

