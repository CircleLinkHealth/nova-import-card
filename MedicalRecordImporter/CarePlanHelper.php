<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\PatientData as NbiPatientData;
use App\Events\PatientUserCreated;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\BloodPressure;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\ProblemsToMonitor;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\Weight;
use CircleLinkHealth\Eligibility\NBISupplementaryDataNotFound;
use CircleLinkHealth\SharedModels\Entities\Allergy;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class CarePlanHelper
{
    const NBI_PRACTICE_NAME = 'bethcare-newark-beth-israel';
    
    const RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS = 'receives_nbi_exceptions_notifications';
    
    public $all;
    public $carePlan;
    public $dem;
    public $imr;
    public $meds;
    public $patientInfo;
    public $probs;
    public $user;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\Enrollee
     */
    private $enrollee;
    private $str;
    /**
     * @var Contracts\MedicalRecord|null
     */
    private $mr;
    /**
     * @var boolean|null
     */
    private $hasUPG0506Instructions;
    
    public function __construct(
        User $user,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $this->all   = $importedMedicalRecord->allergies()->get();
        $this->dem   = $importedMedicalRecord->demographics()->first();
        $this->meds  = $importedMedicalRecord->medications()->get();
        $this->probs = $importedMedicalRecord->problems()->get();
        $this->user  = $user;
        $this->imr   = $importedMedicalRecord;
        $this->mr    = $importedMedicalRecord->medicalRecord();
        $this->str   = new StringManipulation();
    }
    
    /**
     * Create a new CarePlan.
     *
     * @return $this
     */
    public function createNewCarePlan()
    {
        $this->carePlan = CarePlan::firstOrCreate(
            [
                'user_id' => $this->user->id,
            ],
            [
                'care_plan_template_id' => $this->user->service()->firstOrDefaultCarePlan(
                    $this->user
                )->care_plan_template_id,
                'status'                => 'draft',
            ]
        );
        
        return $this;
    }
    
    public function handleEnrollees()
    {
        $enrollee = Enrollee::where(
            function ($q) {
                $q
                    ->where('medical_record_type', $this->imr->medical_record_type)
                    ->whereMedicalRecordId($this->imr->medical_record_id)
                    ->whereNull('user_id');
            }
        )->orWhere(
            [
                [
                    'practice_id',
                    '=',
                    $this->user->program_id,
                ],
                [
                    'first_name',
                    '=',
                    $this->user->first_name,
                ],
                [
                    'last_name',
                    '=',
                    $this->user->last_name,
                ],
                [
                    'dob',
                    '=',
                    $this->dem->dob,
                ],
            ]
        )->orWhere(
            [
                [
                    'practice_id',
                    '=',
                    $this->user->program_id,
                ],
                [
                    'mrn',
                    '=',
                    $this->dem->mrn_number,
                ],
            ]
        )->first();
        
        if ($enrollee) {
            $this->enrollee        = $enrollee;
            $enrollee->user_id     = $this->user->id;
            $enrollee->provider_id = $this->imr->billing_provider_id;
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
        if (empty($this->all)) {
            return $this;
        }
        
        foreach ($this->all as $allergy) {
            $ccdAllergy = Allergy::updateOrCreate(
                [
                    'allergen_name'      => $allergy->allergen_name,
                ],
                [
                    'allergy_import_id' => $allergy->id,
                    'patient_id'         => $this->user->id,
                    'ccd_allergy_log_id' => $allergy->ccd_allergy_log_id,
                ]
            );
        }
    
        $unique = $this->user->ccdAllergies->unique('name')->pluck('id');
    
        $deleted = $this->user->ccdAllergies()->whereNotIn('id', $unique)->delete();
        
        $misc = CpmMisc::whereName(CpmMisc::ALLERGIES)
                       ->first();
    
        if (! $this->hasMisc($this->user, $misc)) {
            $this->user->cpmMiscs()->attach(optional($misc)->id);
        }
        
        return $this;
    }
    
    /**
     * Store Billing Provider.
     *
     * @return $this
     */
    public function storeBillingProvider()
    {
        $providerId = empty($this->imr->billing_provider_id)
            ?: $this->imr->billing_provider_id;
        
        if ($providerId) {
            $billing = CarePerson::firstOrCreate(
                [
                    'type'           => CarePerson::BILLING_PROVIDER,
                ],
                [
                    'user_id'        => $this->user->id,
                    'member_user_id' => $providerId,
                    'alert' => true,
                ]
            );
        }
        
        return $this;
    }
    
    /**
     * Store Contact Windows.
     *
     * @return $this
     */
    public function storeContactWindows()
    {
        // update timezone
        $this->user->timezone = optional($this->imr->location)->timezone ?? 'America/New_York';
        
        if (PatientContactWindow::where('patient_info_id', $this->user->patientInfo->id)->exists()) {
            return $this;
        }
        
        $preferredCallDays  = parseCallDays($this->dem->preferred_call_days);
        $preferredCallTimes = parseCallTimes($this->dem->preferred_call_times);
        
        if ( ! $preferredCallDays && ! $preferredCallTimes) {
            PatientContactWindow::sync(
                $this->patientInfo,
                [
                    1,
                    2,
                    3,
                    4,
                    5,
                ]
            );
            
            return $this;
        }
        
        PatientContactWindow::sync(
            $this->patientInfo,
            $preferredCallDays,
            $preferredCallTimes['start'],
            $preferredCallTimes['end']
        );
        
        return $this;
    }
    
    public function storeImportedValues()
    {
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
        $this->user->display_name = "{$this->user->first_name} {$this->user->last_name}";
        $this->user->program_id   = $this->imr->practice_id ?? null;
        $this->user->save();
        
        $this->handleEnrollees()
             ->updateTrainingFeatures();
        
        event(new PatientUserCreated($this->user));
        
        return $this->carePlan;
    }
    
    /**
     * Stores Insurance.
     *
     * @return $this
     */
    public function storeInsurance()
    {
        $insurance = CcdInsurancePolicy::withMedicalRecord(
            $this->imr->medical_record_id,
            $this->imr->medical_record_type
        )->update(
            [
                'patient_id' => $this->user->id,
            ]
        );
        
        return $this;
    }
    
    /**
     * Store Location.
     *
     * @return $this
     */
    public function storeLocation()
    {
        $locationId = empty($this->imr->location_id)
            ?: $this->imr->location_id;
        
        if ($locationId) {
            $this->user->attachLocation($locationId);
        }
        
        return $this;
    }
    
    /**
     * Stores MedicationImports as Medication Models.
     *
     * @return $this
     */
    public function storeMedications()
    {
        if (empty($this->meds)) {
            return $this;
        }
        
        $medicationGroups = [];
        
        foreach ($this->meds as $medication) {
            if ( ! $medication->name && ! $medication->sig) {
                continue;
            }
            $ccdMedication = Medication::updateOrCreate(
                [
                    'ccd_medication_log_id' => $medication->ccd_medication_log_id,
                ],
                [
                    'medication_import_id' => $medication->id,
                    'medication_group_id'  => $medication->medication_group_id,
                    'name'                 => $medication->name,
                    'sig'                  => $medication->sig,
                    'code'                 => $medication->code,
                    'code_system'          => $medication->code_system,
                    'code_system_name'     => $medication->code_system_name,
                    'patient_id'           => $this->user->id,
                ]
            );
            
            $medicationGroups[] = $medication->medication_group_id;
        }
        
        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
                       ->first();
    
        if (! $this->hasMisc($this->user, $misc)) {
            $this->user->cpmMiscs()->attach(optional($misc)->id);
        }
        
        $this->user->cpmMedicationGroups()->sync(array_filter($medicationGroups));
        
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
        
        $primaryPractice = $this->user->primaryPractice;
        
        if (self::NBI_PRACTICE_NAME == $primaryPractice->name) {
            $dataFromPractice = NbiPatientData::where('first_name', 'like', "{$this->user->first_name}%")
                                              ->where('last_name', $this->user->last_name)
                                              ->where('dob', $this->dem->dob)
                                              ->first();
            
            if ( ! $dataFromPractice) {
                sendNbiPatientMrnWarning($this->user->id);
                
                $recipients = AppConfig::where('config_key', '=', self::RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS)->get();
                
                foreach ($recipients as $recipient) {
                    Notification::route('mail', $recipient->config_value)
                                ->notify(new NBISupplementaryDataNotFound($this->user));
                }
            }
            
            if (optional($dataFromPractice)->mrn) {
                $mrn = $dataFromPractice->mrn;
            }
        }
        
        $agentDetails = $this->getEnrolleeAgentDetailsIfExist();
        
        $this->patientInfo = Patient::firstOrCreate(
            [
                'user_id' => $this->user->id,
            ],
            array_merge(
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
                    'user_id'                    => $this->user->id,
                    'registration_date'          => $this->user->user_registered,
                ],
                $agentDetails
            )
        );
        
        if (! $this->patientInfo->mrn_number) {
            $this->patientInfo->mrn_number = $mrn;
        }
        
        if (! $this->patientInfo->birth_date) {
            $this->patientInfo->birth_date = $this->dem->dob;
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
        $pPhone      = $this->str->extractNumbers($this->dem->primary_phone);
        $homePhone   = null;
        $mobilePhone = null;
        $workPhone   = null;
        
        //`$this->demographicsImport->primary_phone` may be a phone number or phone type
        $primaryPhone = ! empty($pPhone)
            ? $this->str->formatPhoneNumberE164($pPhone)
            : $this->dem->primary_phone;
        
        $homeNumber = $this->dem->home_phone
            ? $this->dem->home_phone
            : $primaryPhone;
        
        if ( ! empty($homeNumber)) {
            if ($this->validatePhoneNumber($homeNumber)) {
                $number = $this->str->formatPhoneNumberE164($homeNumber);
                
                $makePrimary = 0 == strcasecmp(
                        $primaryPhone,
                        PhoneNumber::HOME
                    ) || $primaryPhone == $number || ! $primaryPhone;
                
                $homePhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->user->id,
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
        
        $mobileNumber = $this->dem->cell_phone;
        if ( ! empty($mobileNumber)) {
            if ($this->validatePhoneNumber($mobileNumber)) {
                $number = $this->str->formatPhoneNumberE164($mobileNumber);
                
                $makePrimary = 0 == strcasecmp($primaryPhone, PhoneNumber::MOBILE) || 0 == strcasecmp(
                        $primaryPhone,
                        'cell'
                    ) || $primaryPhone == $number || ! $primaryPhone;
                
                $mobilePhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->user->id,
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
                        'user_id' => $this->user->id,
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
                        'user_id' => $this->user->id,
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
                                'user_id' => $this->user->id,
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
        $practiceId = empty($this->imr->practice_id)
            ?: $this->imr->practice_id;
        
        if ($practiceId) {
            $this->user->attachPractice($practiceId, [Role::whereName('participant')->firstOrFail()->id]);
            $this->user->load('primaryPractice');
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
                    'problem_import_id' => $problem->id,
                ],
                [
                    'is_monitored'       => (bool) $problem->cpm_problem_id,
                    'ccd_problem_log_id' => $problem->ccd_problem_log_id,
                    'name'               => $problem->name,
                    'cpm_problem_id'     => $problem->cpm_problem_id,
                    'patient_id'         => $this->user->id,
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
                            ],
                            [
                                'code_system_name' => $codeLog->code_system_name,
                                'code_system_oid'  => $codeLog->code_system_oid,
                                'code'             => $codeLog->code,
                            ]
                        );
                    }
                );
            }
        }
        
        
        
        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
                       ->first();
    
        if (! $this->hasMisc($this->user, $misc)) {
            $this->user->cpmMiscs()->attach(optional($misc)->id);
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
        
        $storage = new ProblemsToMonitor($this->user->program_id, $this->user);
        
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
        $this->mr->patient_id = $this->user->id;
        $this->mr->save();
        
        $decodedCcda = $this->mr->bluebuttonJson();
        
        //Weight
        $weightParseAndStore = new Weight($this->user->program_id, $this->user);
        $weight              = $weightParseAndStore->parse($decodedCcda);
        if ( ! empty($weight)) {
            $weightParseAndStore->import($weight);
        }
        
        //Blood Pressure
        $bloodPressureParseAndStore = new BloodPressure($this->user->program_id, $this->user);
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
    
    private function hasMisc(User $user, ?CpmMisc $misc)
    {
        return $user->cpmMiscs()->where('cpm_miscs.id', optional($misc)->id)->exists();
    }
}
