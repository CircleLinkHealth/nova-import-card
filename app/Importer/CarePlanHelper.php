<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer;

use App\CarePerson;
use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\Biometrics\BloodPressure;
use App\CLH\CCD\Importer\StorageStrategies\Biometrics\Weight;
use App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitor;
use App\CLH\Helpers\StringManipulation;
use App\Enrollee;
use App\Models\CCD\Allergy;
use App\Models\CCD\CcdInsurancePolicy;
use App\Models\CCD\Medication;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Models\ProblemCode;
use App\Patient;
use App\PatientContactWindow;
use App\PhoneNumber;
use App\User;
use Illuminate\Validation\Rule;

class CarePlanHelper
{
    public $all;
    public $carePlan;
    public $dem;
    public $imr;
    public $meds;
    public $patientInfo;
    public $probs;
    public $user;
    private $str;

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
        $this->str   = new StringManipulation();
    }

    /**
     * Create a new CarePlan.
     *
     * @return $this
     */
    public function createNewCarePlan()
    {
        $this->carePlan = CarePlan::updateOrCreate([
            'user_id' => $this->user->id,
        ], [
            'care_plan_template_id' => $this->user->service()->firstOrDefaultCarePlan($this->user)->care_plan_template_id,
            'status'                => 'draft',
        ]);

        return $this;
    }

    public function handleEnrollees()
    {
        $enrollee = Enrollee::where(function ($q) {
            $q
                ->where('medical_record_type', $this->imr->medical_record_type)
                ->whereMedicalRecordId($this->imr->medical_record_id)
                ->whereNull('user_id');
        })->orWhere([
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
        ])->orWhere([
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
        ])->first();

        if ($enrollee) {
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
            $ccdAllergy = Allergy::create([
                'allergy_import_id'  => $allergy->id,
                'patient_id'         => $this->user->id,
                'ccd_allergy_log_id' => $allergy->ccd_allergy_log_id,
                'allergen_name'      => $allergy->allergen_name,
            ]);
        }

        $misc = CpmMisc::whereName(CpmMisc::ALLERGIES)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id);

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
            $billing = CarePerson::create([
                'alert'          => true,
                'user_id'        => $this->user->id,
                'member_user_id' => $providerId,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]);
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
        $this->user->timezone = 'America/New_York';

        $preferredCallDays  = parseCallDays($this->dem->preferred_call_days);
        $preferredCallTimes = parseCallTimes($this->dem->preferred_call_times);

        if ( ! $preferredCallDays && ! $preferredCallTimes) {
            PatientContactWindow::sync($this->patientInfo, [
                1,
                2,
                3,
                4,
                5,
            ]);

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
            ->storeProblemsToMonitor()
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
        )->update([
            'patient_id' => $this->user->id,
        ]);

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
            $ccdMedication = Medication::create([
                'medication_import_id'  => $medication->id,
                'ccd_medication_log_id' => $medication->ccd_medication_log_id,
                'medication_group_id'   => $medication->medication_group_id,
                'name'                  => $medication->name,
                'sig'                   => $medication->sig,
                'code'                  => $medication->code,
                'code_system'           => $medication->code_system,
                'code_system_name'      => $medication->code_system_name,
                'patient_id'            => $this->user->id,
            ]);

            $medicationGroups[] = $medication->medication_group_id;
        }

        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id);
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
        $this->patientInfo = Patient::updateOrCreate([
            'user_id' => $this->user->id,
        ], [
            'imported_medical_record_id' => $this->imr->id,
            'ccda_id'                    => Ccda::class == $this->imr->medical_record_type
                ? $this->imr->medical_record_id
                : null,
            'birth_date'                 => $this->dem->dob,
            'ccm_status'                 => 'enrolled',
            'consent_date'               => $this->dem->consent_date,
            'gender'                     => $this->dem->gender,
            'mrn_number'                 => $this->dem->mrn_number,
            'preferred_contact_language' => $this->dem->preferred_contact_language,
            'preferred_contact_location' => $this->imr->location_id,
            'preferred_contact_method'   => 'CCT',
            'user_id'                    => $this->user->id,
            'registration_date'          => $this->user->user_registered,
        ]);

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

                $homePhone = PhoneNumber::create([
                    'user_id'    => $this->user->id,
                    'number'     => $number,
                    'type'       => PhoneNumber::HOME,
                    'is_primary' => $makePrimary,
                ]);

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

                $mobilePhone = PhoneNumber::create([
                    'user_id'    => $this->user->id,
                    'number'     => $number,
                    'type'       => PhoneNumber::MOBILE,
                    'is_primary' => $makePrimary,
                ]);
            }
        }

        $workNumber = $this->dem->work_phone;
        if ( ! empty($workNumber)) {
            if ($this->validatePhoneNumber($mobileNumber)) {
                $number = $this->str->formatPhoneNumberE164($workNumber);

                $makePrimary = PhoneNumber::WORK == $primaryPhone || $primaryPhone == $number || ! $primaryPhone;

                $workPhone = PhoneNumber::create([
                    'user_id'    => $this->user->id,
                    'number'     => $number,
                    'type'       => PhoneNumber::WORK,
                    'is_primary' => $makePrimary,
                ]);
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
                PhoneNumber::create([
                    'user_id'    => $this->user->id,
                    'number'     => $this->str->formatPhoneNumberE164($this->dem->primary_phone),
                    'type'       => PhoneNumber::HOME,
                    'is_primary' => true,
                ]);
            }
        } else {
            if ($this->validatePhoneNumber($primaryPhone)) {
                $number = $this->str->formatPhoneNumberE164($primaryPhone);

                foreach (
                    [
                        PhoneNumber::HOME => $homePhone,
                        PhoneNumber::MOBILE => $mobilePhone,
                        PhoneNumber::WORK => $workPhone,
                    ] as $type => $phone
                ) {
                    if ( ! $phone) {
                        PhoneNumber::create([
                            'user_id'    => $this->user->id,
                            'number'     => $number,
                            'type'       => $type,
                            'is_primary' => true,
                        ]);

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
            $this->user->attachPractice($practiceId, false, false, 2);
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

        $cpmProblems = CpmProblem::get()->keyBy('id');

        foreach ($this->probs as $problem) {
            $cpmProblem = $problem->cpm_problem_id
                ? $cpmProblems[$problem->cpm_problem_id]
                : null;
            $defaultInstruction = optional($cpmProblem)->instruction();

            $ccdProblem = Problem::create([
                'is_monitored'       => (bool) $problem->cpm_problem_id,
                'problem_import_id'  => $problem->id,
                'ccd_problem_log_id' => $problem->ccd_problem_log_id,
                'name'               => $problem->name,
                'cpm_problem_id'     => $problem->cpm_problem_id,
                'patient_id'         => $this->user->id,
                'cpm_instruction_id' => $defaultInstruction->id ?? null,
            ]);

            $problemLog = $problem->ccdLog;

            if ($problemLog) {
                $problemLog->codes->map(function ($codeLog) use ($ccdProblem) {
                    ProblemCode::create([
                        'problem_id'       => $ccdProblem->id,
                        'code_system_name' => $codeLog->code_system_name,
                        'code_system_oid'  => $codeLog->code_system_oid,
                        'code'             => $codeLog->code,
                    ]);
                });
            }
        }

        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id);

        return $this;
    }

    /**
     * Activates Problems to Monitor (CCM Conditions).
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

        $ccda = $this->imr->medicalRecord();

        if ( ! $ccda) {
            return $this;
        }

        //doing this here to not break View CCDA button
        $ccda->patient_id = $this->user->id;
        $ccda->save();

        $decodedCcda = $ccda->bluebuttonJson();

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

    private function updateTrainingFeatures()
    {
        $this
            ->imr
            ->medicalRecord()
            ->document
            ->each(function ($documentLog) {
                $documentLog->practice_id = $this->imr->practice_id;
                $documentLog->location_id = $this->imr->location_id;
                $documentLog->billing_provider_id = $this->imr->billing_provider_id;

                $documentLog->save();
            });

        $this
            ->imr
            ->medicalRecord()
            ->providers
            ->each(function ($providerLog) {
                $providerLog->practice_id = $this->imr->practice_id;
                $providerLog->location_id = $this->imr->location_id;
                $providerLog->billing_provider_id = $this->imr->billing_provider_id;

                $providerLog->save();
            });

        $mr = $this
            ->imr
            ->medicalRecord();

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
        $validator = \Validator::make(['number' => $phoneNumber], [
            'number' => ['required', Rule::phone()->country(['US'])],
        ]);

        return $validator->passes();
    }
}
