<?php

namespace App\Importer;


use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\Biometrics\BloodPressure;
use App\CLH\CCD\Importer\StorageStrategies\Biometrics\Weight;
use App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitor;
use App\Models\CCD\Allergy;
use App\Models\CCD\CcdInsurancePolicy;
use App\Models\CCD\Medication;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmMisc;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Patient;
use App\PatientCareTeamMember;
use App\PatientContactWindow;
use App\PhoneNumber;
use App\User;

class CarePlanHelper
{
    private $allergiesImport;
    private $demographicsImport;
    private $medicationsImport;
    private $problemsImport;
    private $user;
    private $importedMedicalRecord;
    private $carePlan;
    private $patientInfo;

    public function __construct(
        User $user,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $this->allergiesImport = $importedMedicalRecord->allergies->all();
        $this->demographicsImport = $importedMedicalRecord->demographics;
        $this->medicationsImport = $importedMedicalRecord->medications->all();
        $this->problemsImport = $importedMedicalRecord->problems->all();
        $this->user = $user;
        $this->importedMedicalRecord = $importedMedicalRecord;
    }

    public function storeImportedValues()
    {
        $this->createNewCarePlan()
            ->storeAllergies()
            ->storeProblemsList()
            ->storeProblemsToMonitor()
            ->storeMedications()
            ->storeBillingProvider()
            ->storePatientInfo()
            ->storeContactWindows()
            ->storePhones()
            ->storeInsurance();

        /**
         * Populate display_name on User
         */
        $this->user->display_name = "{$this->user->first_name} {$this->user->last_name}";
        $this->user->save();

        /**
         * CarePlan Defaults
         */

        /**
         * Biometrics
         */

        //Weight
        $weightParseAndStore = new Weight($this->user->program_id, $this->user);
        $weight = $weightParseAndStore->parse($this->decodedCcda);
        if (!empty($weight)) {
            $weightParseAndStore->import($weight);
        }

        //Blood Pressure
        $bloodPressureParseAndStore = new BloodPressure($this->user->program_id, $this->user);
        $bloodPressure = $bloodPressureParseAndStore->parse($this->decodedCcda);
        if (!empty($bloodPressure)) {
            $bloodPressureParseAndStore->import($bloodPressure);
        }


        return $this->carePlan;
    }

    /**
     * Stores Insurance
     *
     * @return $this
     */
    public function storeInsurance()
    {
        $insurance = CcdInsurancePolicy::withMedicalRecord(
            $this->importedMedicalRecord->medical_record_id,
            $this->importedMedicalRecord->medical_record_type
        )->update([
                'patient_id' => $this->user->id,
            ]
        );

        return $this;
    }

    /**
     * Store Phone Numbers
     *
     * @return $this
     */
    public function storePhones()
    {
        if (!empty($homeNumber = $this->demographicsImport->home_phone)) {
            $homePhone = PhoneNumber::create([
                'user_id' => $this->user->id,
                'number'  => $homeNumber,
                'type'    => PhoneNumber::HOME,
            ]);
        }

        if (!empty($mobileNumber = $this->demographicsImport->cell_phone)) {
            $mobilePhone = PhoneNumber::create([
                'user_id' => $this->user->id,
                'number'  => $mobileNumber,
                'type'    => PhoneNumber::MOBILE,
            ]);
        }

        if (!empty($workNumber = $this->demographicsImport->work_phone)) {
            $workPhone = PhoneNumber::create([
                'user_id' => $this->user->id,
                'number'  => $workNumber,
                'type'    => PhoneNumber::WORK,
            ]);
        }

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

        return $this;
    }

    /**
     * Store Contact Windows
     *
     * @return $this
     */
    public function storeContactWindows()
    {
        // update timezone
        $this->user->timezone = 'America/New_York';

        PatientContactWindow::sync($this->patientInfo, [
            1,
            2,
            3,
            4,
            5,
        ]);

        return $this;
    }

    /**
     * Store Patient Info
     *
     * @return $this
     */
    public function storePatientInfo()
    {
        $this->patientInfo = Patient::updateOrCreate([
            'user_id' => $this->user->id,
        ], [
            'imported_medical_record_id' => $this->importedMedicalRecord->id,
            'birth_date'                 => $this->demographicsImport->dob,
            'ccm_status'                 => 'enrolled',
            'consent_date'               => $this->demographicsImport->consent_date,
            'gender'                     => $this->demographicsImport->gender,
            'mrn_number'                 => $this->demographicsImport->mrn_number,
            'preferred_contact_language' => $this->demographicsImport->preferred_contact_language,
            'preferred_contact_location' => $this->demographicsImport->location_id,
            'preferred_contact_method'   => 'CCT',
            'user_id'                    => $this->user->id,
        ]);

        return $this;
    }

    /**
     * Store Billing Provider
     *
     * @return $this
     */
    public function storeBillingProvider()
    {
        $providerId = empty($this->demographicsImport->provider_id)
            ? null
            : $this->demographicsImport->provider_id;

        if ($providerId) {
            //care team
            $member = PatientCareTeamMember::create([
                'user_id'        => $this->user->id,
                'member_user_id' => $providerId,
                'type'           => PatientCareTeamMember::MEMBER,
            ]);

            $sendAlertTo = PatientCareTeamMember::create([
                'user_id'        => $this->user->id,
                'member_user_id' => $providerId,
                'type'           => PatientCareTeamMember::SEND_ALERT_TO,
            ]);

            $billing = PatientCareTeamMember::create([
                'user_id'        => $this->user->id,
                'member_user_id' => $providerId,
                'type'           => PatientCareTeamMember::BILLING_PROVIDER,
            ]);

            $lead = PatientCareTeamMember::create([
                'user_id'        => $this->user->id,
                'member_user_id' => $providerId,
                'type'           => PatientCareTeamMember::LEAD_CONTACT,
            ]);
        }

        return $this;
    }

    /**
     * Stores MedicationImports as Medication Models
     *
     * @return $this
     */
    private function storeMedications()
    {
        if (empty($this->medicationsImport)) {
            return $this;
        }

        foreach ($this->medicationsImport as $medication) {
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
        }

        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id);

        return $this;
    }

    /**
     * Activates Problems to Monitor (CCM Conditions)
     *
     * @return $this
     */
    private function storeProblemsToMonitor()
    {
        if (empty($this->problemsImport)) {
            return $this;
        }

        $storage = new ProblemsToMonitor($this->user->program_id, $this->user);

        $problemsToActivate = [];

        foreach ($this->problemsImport as $problem) {
            if (empty($problem->cpm_problem_id)) {
                continue;
            }

            $problemsToActivate[] = $problem->cpm_problem_id;
        }

        $storage->import(array_unique($problemsToActivate));

        return $this;
    }

    /**
     * Store ProblemImports as Problem Models
     *
     * @return $this
     */
    private function storeProblemsList()
    {
        if (empty($this->problemsImport)) {
            return $this;
        }

        foreach ($this->problemsImport as $problem) {
            $ccdProblem = Problem::create([
                'problem_import_id'  => $problem->id,
                'ccd_problem_log_id' => $problem->ccd_problem_log_id,
                'name'               => $problem->name,
                'code'               => $problem->code,
                'code_system'        => $problem->code_system,
                'code_system_name'   => $problem->code_system_name,
                'activate'           => $problem->activate,
                'cpm_problem_id'     => $problem->cpm_problem_id,
                'patient_id'         => $this->user->id,
            ]);
        }

        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id);

        return $this;
    }

    /**
     * Store AllergyImports as Allergy Models
     *
     * @return $this
     */
    private function storeAllergies()
    {
        if (empty($this->allergiesImport)) {
            return $this;
        }

        foreach ($this->allergiesImport as $allergy) {
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
     * Create a new CarePlan
     *
     * @return $this
     */
    private function createNewCarePlan()
    {
        $this->carePlan = CarePlan::updateOrCreate([
            'user_id' => $this->user->id,
        ], [
            'care_plan_template_id' => $this->user->service()->firstOrDefaultCarePlan($this->user)->getCarePlanTemplateIdAttribute(),
            'status'                => 'draft',
        ]);

        return $this;
    }
}