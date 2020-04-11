<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use App\Events\PatientUserCreated;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachDefaultPatientContactWindows;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachLocation;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\FirstOrCreateCarePlan;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportAllergies;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachBillingProvider;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportInsurances;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportMedications;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\BloodPressure;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\ProblemsToMonitor;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\Weight;
use CircleLinkHealth\Eligibility\NBISupplementaryDataNotFound;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

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
     * @var boolean|null
     */
    protected $hasUPG0506Instructions;
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
        $pPhone      = optional($this->enrollee)->primary_phone ?? $this->str->extractNumbers($this->dem->primary_phone);
        $homePhone   = null;
        $mobilePhone = null;
        $workPhone   = null;
        
        //`$this->demographicsImport->primary_phone` may be a phone number or phone type
        $primaryPhone = ! empty($pPhone)
            ? $this->str->formatPhoneNumberE164($pPhone)
            : $this->dem->primary_phone;
        
        $homeNumber = optional($this->enrollee)->home_phone ?? $this->dem->home_phone ?? $primaryPhone;
        
        if ( ! empty($homeNumber)) {
            if ($this->validatePhoneNumber($homeNumber)) {
                $number = $this->str->formatPhoneNumberE164($homeNumber);
                
                $makePrimary = 0 == strcasecmp(
                        $primaryPhone,
                        PhoneNumber::HOME
                    ) || $primaryPhone == $number || ! $primaryPhone;
                
                $homePhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $number,
                        'type'    => PhoneNumber::HOME,
                    ],
                    [
                        'is_primary' => $makePrimary,
                    ]
                );
                
                /**
                 * Band-aid solution to avoid making all phones primary, if there is no primary phone.
                 * In `$makePrimary = strcasecmp($primaryPhone, PhoneNumber::HOME) == 0 || $primaryPhone == $number || ! $primaryPhone;`, `! $primaryPhone`
                 * would make all phones primary, if `! $primaryPhone`.
                 */
                if ($makePrimary) {
                    $primaryPhone = $number;
                }
            }
        }
        
        $mobileNumber = optional($this->enrollee)->cell_phone ?? $this->dem->cell_phone;
        if ( ! empty($mobileNumber)) {
            if ($this->validatePhoneNumber($mobileNumber)) {
                $number = $this->str->formatPhoneNumberE164($mobileNumber);
                
                $makePrimary = 0 == strcasecmp($primaryPhone, PhoneNumber::MOBILE) || 0 == strcasecmp(
                        $primaryPhone,
                        'cell'
                    ) || $primaryPhone == $number || ! $primaryPhone;
                
                $mobilePhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $number,
                        'type'    => PhoneNumber::MOBILE,
                    ],
                    [
                        'is_primary' => $makePrimary,
                    ]
                );
            }
        }
        
        $workNumber = $this->dem->work_phone;
        if ( ! empty($workNumber)) {
            if ($this->validatePhoneNumber($mobileNumber)) {
                $number = $this->str->formatPhoneNumberE164($workNumber);
                
                $makePrimary = PhoneNumber::WORK == $primaryPhone || $primaryPhone == $number || ! $primaryPhone;
                
                $workPhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $number,
                        'type'    => PhoneNumber::WORK,
                    ],
                    [
                        'is_primary' => $makePrimary,
                    ]
                );
            }
        }
        
        if ( ! $primaryPhone) {
            $primaryPhone = empty($mobileNumber)
                ? empty($homeNumber)
                    ? empty($workNumber)
                        ? false
                        : $workPhone
                    : $homePhone
                : $mobilePhone;
            
            if ($primaryPhone) {
                $primaryPhone->setAttribute('is_primary', true);
                $primaryPhone->save();
            }
            
            if ( ! $primaryPhone && $this->dem->primary_phone) {
                PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $this->str->formatPhoneNumberE164($this->dem->primary_phone),
                        'type'    => PhoneNumber::HOME,
                    ],
                    [
                        'is_primary' => true,
                    ]
                );
            }
        } else {
            if ($this->validatePhoneNumber($primaryPhone)) {
                $number = $this->str->formatPhoneNumberE164($primaryPhone);
                
                foreach (
                    [
                        PhoneNumber::HOME   => $homePhone,
                        PhoneNumber::MOBILE => $mobilePhone,
                        PhoneNumber::WORK   => $workPhone,
                    ] as $type => $phone
                ) {
                    if ( ! $phone) {
                        PhoneNumber::updateOrCreate(
                            [
                                'user_id' => $this->patient->id,
                                'number'  => $number,
                                'type'    => $type,
                            ],
                            [
                                'is_primary' => true,
                            ]
                        );
                        
                        break;
                    }
                    if ($phone->number == $number) {
                        //number is already saved. bail
                        break;
                    }
                }
            }
        }
        
        return $this;
    }
    
    public function storePractice()
    {
        $practiceId = $this->imr->practice_id ?? null;
        
        if ($practiceId) {
            $this->patient->attachPractice($practiceId, [Role::whereName('participant')->firstOrFail()->id]);
            $this->patient->load('primaryPractice');
        }
        
        return $this;
    }
    
    /**
     * Store ProblemImports as Problem Models.
     *
     * @return $this
     */
    public function storeProblemsList()
    {
        if (empty($this->probs)) {
            return $this;
        }
        
        /** @var ProblemImport $problem */
        foreach ($this->probs as $problem) {
            $instruction = $this->getInstruction($problem);
            
            $ccdProblem = Problem::updateOrCreate(
                [
                    'name'           => $problem->name,
                    'patient_id'     => $this->patient->id,
                    'cpm_problem_id' => $problem->cpm_problem_id,
                ],
                [
                    'problem_import_id'  => $problem->id,
                    'is_monitored'       => (bool)$problem->cpm_problem_id,
                    'ccd_problem_log_id' => $problem->ccd_problem_log_id,
                    'cpm_instruction_id' => optional($instruction)->id ?? null,
                ]
            );
            
            $problemLog = $problem->ccdLog;
            
            if ($problemLog) {
                $problemLog->codes->map(
                    function ($codeLog) use ($ccdProblem) {
                        ProblemCode::updateOrCreate(
                            [
                                'problem_id' => $ccdProblem->id,
                                'code'       => $codeLog->code,
                            ],
                            [
                                'code_system_name' => $codeLog->code_system_name,
                                'code_system_oid'  => $codeLog->code_system_oid,
                            ]
                        );
                    }
                );
            }
        }
        
        
        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
                       ->first();
        
        if ( ! $this->hasMisc($this->patient, $misc)) {
            $this->patient->cpmMiscs()->attach(optional($misc)->id);
        }
        
        return $this;
    }
    
    /**
     * Activates Problems to Monitor (CCM Conditions).
     * Still used by: ReImportCcdToGetProblemTranslationCodes.php.
     *
     * @return $this
     */
    public function storeProblemsToMonitor()
    {
        if (empty($this->probs)) {
            return $this;
        }
        
        $storage = new ProblemsToMonitor($this->patient->program_id, $this->patient);
        
        $problemsToActivate = [];
        
        foreach ($this->probs as $problem) {
            if (empty($problem->cpm_problem_id)) {
                continue;
            }
            
            $problemsToActivate[] = $problem->cpm_problem_id;
        }
        
        $storage->import(array_unique($problemsToActivate));
        
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
        if (Ccda::class != $this->imr->medical_record_type) {
            return $this;
        }
        
        if ( ! $this->mr) {
            return $this;
        }
        
        //doing this here to not break View CCDA button
        $this->mr->patient_id = $this->patient->id;
        $this->mr->save();
        
        $decodedCcda = $this->mr->bluebuttonJson();
        
        //Weight
        $weightParseAndStore = new Weight($this->patient->program_id, $this->patient);
        $weight              = $weightParseAndStore->parse($decodedCcda);
        if ( ! empty($weight)) {
            $weightParseAndStore->import($weight);
        }
        
        //Blood Pressure
        $bloodPressureParseAndStore = new BloodPressure($this->patient->program_id, $this->patient);
        $bloodPressure              = $bloodPressureParseAndStore->parse($decodedCcda);
        if ( ! empty($bloodPressure)) {
            $bloodPressureParseAndStore->import($bloodPressure);
        }
        
        return $this;
    }
    
    private function updateTrainingFeatures()
    {
        $this
            ->mr
            ->document
            ->each(
                function ($documentLog) {
                    $documentLog->practice_id         = $this->imr->practice_id;
                    $documentLog->location_id         = $this->imr->location_id;
                    $documentLog->billing_provider_id = $this->imr->billing_provider_id;
                    
                    $documentLog->save();
                }
            );
        
        $this
            ->mr
            ->providers
            ->each(
                function ($providerLog) {
                    $providerLog->practice_id         = $this->imr->practice_id;
                    $providerLog->location_id         = $this->imr->location_id;
                    $providerLog->billing_provider_id = $this->imr->billing_provider_id;
                    
                    $providerLog->save();
                }
            );
        
        $mr = $this
            ->mr;
        
        if ($mr) {
            $mr->practice_id         = $this->imr->practice_id;
            $mr->location_id         = $this->imr->location_id;
            $mr->billing_provider_id = $this->imr->billing_provider_id;
            
            $mr->save();
        }
        
        return $this;
    }
    
    private function validatePhoneNumber($phoneNumber)
    {
        $validator = \Validator::make(
            ['number' => $phoneNumber],
            [
                'number' => ['required', Rule::phone()->country(['US'])],
            ]
        );
        
        return $validator->passes();
    }
    
    private function getInstruction(ProblemImport $problemImport)
    {
        if (is_null($this->hasUPG0506Instructions)) {
            $this->hasUPG0506Instructions = $this->mr->hasUPG0506PdfCareplanMedia()->exists();
        }
        
        if (true === $this->hasUPG0506Instructions) {
            return $this->createInstructionFromUPG0506($problemImport);
        }
        
        $cpmProblems = \Cache::remember(
            'all_cpm_problems_keyed_by_id',
            2,
            function () {
                return CpmProblem::get()->keyBy('id');
            }
        );
        
        $cpmProblem = $problemImport->cpm_problem_id
            ? $cpmProblems[$problemImport->cpm_problem_id]
            : null;
        
        return optional($cpmProblem)->instruction();
    }
    
    private function createInstructionFromUPG0506(ProblemImport $problemImport): ?CpmInstruction
    {
        $pdfMedia = $this->mr->getUPG0506PdfCareplanMedia();
        
        if ( ! $pdfMedia) {
            return null;
        }
        
        $customProperties = json_decode($pdfMedia->custom_properties);
        
        if ( ! isset($customProperties->care_plan)) {
            return null;
        }
        
        $matchingProblem = collect($customProperties->care_plan->instructions)
            ->where('name', $problemImport->name)
            ->first();
        
        
        if ( ! $matchingProblem) {
            return null;
        }
        
        return CpmInstruction::create(
            [
                'name' => $matchingProblem->instructions,
            ]
        );
    }
}

