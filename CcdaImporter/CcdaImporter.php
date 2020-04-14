<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use App\CLH\Repositories\UserRepository;
use App\Events\PatientUserCreated;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Role;
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
use Symfony\Component\HttpFoundation\ParameterBag;

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
        User $patient = null
    ) {
        $this->str     = new StringManipulation();
        $this->ccda    = $ccda;
        $this->patient = $patient;
    }
    
    /**
     * Create a new CarePlan.
     *
     * @return $this
     */
    private function createNewCarePlan()
    {
        $this->carePlan = FirstOrCreateCarePlan::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    private function handleEnrollees()
    {
        $enrollee = Enrollee::duplicates($this->patient, $this->ccda)->first();
        
        if ($enrollee) {
            if (strtolower($this->patient->first_name) != strtolower($enrollee->first_name) || strtolower(
                                                                                                   $this->patient->last_name
                                                                                               ) != strtolower(
                                                                                                   $enrollee->last_name
                                                                                               )) {
                throw new \Exception(
                    "Something fishy is going on. enrollee:{$enrollee->id} has user:{$enrollee->user_id}, which does not matched with user:{$this->patient->id}"
                );
            }
            $this->enrollee    = $enrollee;
            $enrollee->user_id = $this->patient->id;
            $enrollee->save();
        }
        
        return $this;
    }
    
    /**
     * Store AllergyImports as Allergy Models.
     *
     * @return $this
     */
    private function storeAllergies()
    {
        ImportAllergies::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Billing Provider.
     *
     * @return $this
     */
    private function storeBillingProvider()
    {
        AttachBillingProvider::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Contact Windows.
     *
     * @return $this
     */
    private function storeContactWindows()
    {
        AttachDefaultPatientContactWindows::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    public function attemptCreateCarePlan()
    {
        \DB::transaction(
            function () {
                if (is_null($this->patient)) {
                    $this->createNewPatient();
                }
    
                $this->patient->loadMissing(['primaryPractice', 'patientInfo']);
                $this->ccda->loadMissing(['location', 'patient']);
                
                $this->handleEnrollees()
                     ->createNewCarePlan()
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
            }
        );
        
        return $this->carePlan;
    }
    
    /**
     * Stores Insurance.
     *
     * @return $this
     */
    private function storeInsurance()
    {
        ImportInsurances::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Location.
     *
     * @return $this
     */
    private function storeLocation()
    {
        AttachLocation::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Stores MedicationImports as Medication Models.
     *
     * @return $this
     */
    private function storeMedications()
    {
        ImportMedications::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Patient Info.
     *
     * @return $this
     */
    private function storePatientInfo()
    {
        ImportPatientInfo::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store Phone Numbers.
     *
     * @return $this
     */
    private function storePhones()
    {
        ImportPhones::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    private function storePractice()
    {
        AttachPractice::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    /**
     * Store ProblemImports as Problem Models.
     *
     * @return $this
     */
    private function storeProblemsList()
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
    private function storeVitals()
    {
        ImportVitals::for($this->patient, $this->ccda);
        
        return $this;
    }
    
    private function createNewPatient()
    {
        $params = [
            'email'       => $this->ccda->patientEmail(),
            'first_name'  => $this->ccda->patientFirstName(),
            'last_name'   => $this->ccda->patientLastName(),
            'practice_id' => $this->ccda->practice_id,
        ];
        
        $newUserId = str_random(25);
        
        $email = empty($email = $params['email'])
            ? $newUserId.'@careplanmanager.com'
            : $email;
        
        $this->patient = (new UserRepository())->createNewUser(
            new ParameterBag(
                [
                    'email'             => $email,
                    'password'          => str_random(),
                    'display_name'      => ucwords(strtolower($params['first_name'].' '.$params['last_name'])),
                    'first_name'        => $params['first_name'],
                    'last_name'         => $params['last_name'],
                    'username'          => empty($email)
                        ? $newUserId
                        : $email,
                    'program_id'        => $params['practice_id'],
                    'is_auto_generated' => true,
                    'roles'             => [Role::whereName('participant')->firstOrFail()->id],
                    'is_awv'            => Ccda::IMPORTER_AWV === $this->ccda->source,
                ]
            )
        );
        
        Ccda::where('id', $this->ccda->id)->update([
            'patient_id' => $this->patient->id,
                                                    ]);
    }
}

