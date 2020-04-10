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
    const NBI_PRACTICE_NAME = 'bethcare-newark-beth-israel';
    
    const RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS = 'receives_nbi_exceptions_notifications';

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
        $mrn = $this->dem->mrn_number;
        
        $primaryPractice = $this->patient->primaryPractice;
        
        if (self::NBI_PRACTICE_NAME == $primaryPractice->name) {
            $dataFromPractice = SupplementalPatientData::where('first_name', 'like', "{$this->patient->first_name}%")
                                                       ->where('last_name', $this->patient->last_name)
                                                       ->where('dob', $this->dem->dob)
                                                       ->where('practice_id', Practice::whereName(self::NBI_PRACTICE_NAME)->value('id'))
                                                       ->first();
            
            if ( ! $dataFromPractice) {
                sendNbiPatientMrnWarning($this->patient->id);
                
                $recipients = AppConfig::where('config_key', '=', self::RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS)->get();
                
                foreach ($recipients as $recipient) {
                    Notification::route('mail', $recipient->config_value)
                                ->notify(new NBISupplementaryDataNotFound($this->patient));
                }
            }
            
            if (optional($dataFromPractice)->mrn) {
                $mrn = $dataFromPractice->mrn;
            }
        }
        
        $agentDetails = $this->getEnrolleeAgentDetailsIfExist();
        
        $args = array_merge(
            [
                'imported_medical_record_id' => $this->imr->id,
                'ccda_id'                    => Ccda::class == $this->imr->medical_record_type
                    ? $this->imr->medical_record_id
                    : null,
                'birth_date'                 => $this->dem->dob,
                'ccm_status'                 => 'enrolled',
                'consent_date'               => $this->dem->consent_date,
                'gender'                     => $this->dem->gender,
                'mrn_number'                 => $mrn,
                'preferred_contact_language' => $this->dem->preferred_contact_language,
                'preferred_contact_location' => $this->imr->location_id,
                'preferred_contact_method'   => 'CCT',
                'registration_date'          => $this->patient->user_registered,
                'general_comment'            => $this->enrollee
                    ? $this->enrollee->last_call_outcome_reason
                    : null,
            ],
            $agentDetails
        );
        
        $this->patientInfo = Patient::firstOrCreate(
            [
                'user_id' => $this->patient->id,
            ],
            $args
        );
        
        if ( ! $this->patientInfo->mrn_number) {
            $this->patientInfo->mrn_number = $args['mrn_number'];
        }
        
        if ( ! $this->patientInfo->birth_date) {
            $this->patientInfo->birth_date = $args['birth_date'];
        }
        
        if ( ! $this->patientInfo->imported_medical_record_id) {
            $this->patientInfo->imported_medical_record_id = $args['imported_medical_record_id'];
        }
        
        if ( ! $this->patientInfo->ccda_id) {
            $this->patientInfo->ccda_id = $args['ccda_id'];
        }
        
        if ( ! $this->patientInfo->ccm_status) {
            $this->patientInfo->ccm_status = $args['ccm_status'];
        }
        
        if ( ! $this->patientInfo->consent_date) {
            $this->patientInfo->consent_date = $args['consent_date'];
        }
        
        if ( ! $this->patientInfo->gender) {
            $this->patientInfo->gender = $args['gender'];
        }
        
        if ( ! $this->patientInfo->preferred_contact_language) {
            $this->patientInfo->preferred_contact_language = $args['preferred_contact_language'];
        }
        
        if ( ! $this->patientInfo->preferred_contact_location) {
            $this->patientInfo->preferred_contact_location = $args['preferred_contact_location'];
        }
        
        if ( ! $this->patientInfo->preferred_contact_method) {
            $this->patientInfo->preferred_contact_method = $args['preferred_contact_method'];
        }
        
        if ( ! $this->patientInfo->agent_name) {
            $this->patientInfo->agent_name = $args['agent_name'] ?? null;
        }
        
        if ( ! $this->patientInfo->agent_telephone) {
            $this->patientInfo->agent_telephone = $args['agent_telephone'] ?? null;
        }
        
        if ( ! $this->patientInfo->agent_email) {
            $this->patientInfo->agent_email = $args['agent_email'] ?? null;
        }
        
        if ( ! $this->patientInfo->agent_relationship) {
            $this->patientInfo->agent_relationship = $args['agent_relationship'] ?? null;
        }
        
        if ( ! $this->patientInfo->registration_date) {
            $this->patientInfo->registration_date = $args['registration_date'];
        }
        
        if ( ! $this->patientInfo->general_comment) {
            $this->patientInfo->general_comment = $this->enrollee
                ? $this->enrollee->last_call_outcome_reason
                : null;
        }
        
        
        if ($this->patientInfo->isDirty()) {
            $this->patientInfo->save();
        }
        
        
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
    
    /**
     * If Enrollee exists and if agent details are set,
     * Get array to save in patient info.
     *
     * @return array
     */
    private function getEnrolleeAgentDetailsIfExist()
    {
        if ( ! $this->enrollee) {
            return [];
        }
        if (empty($this->enrollee->agent_details)) {
            return [];
        }
        
        return [
            'agent_name'         => $this->enrollee->getAgentAttribute(Enrollee::AGENT_NAME_KEY),
            'agent_telephone'    => $this->enrollee->getAgentAttribute(Enrollee::AGENT_PHONE_KEY),
            'agent_email'        => $this->enrollee->getAgentAttribute(Enrollee::AGENT_EMAIL_KEY),
            'agent_relationship' => $this->enrollee->getAgentAttribute(Enrollee::AGENT_RELATIONSHIP_KEY),
        ];
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

