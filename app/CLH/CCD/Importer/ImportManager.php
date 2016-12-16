<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\Importer\StorageStrategies\Biometrics\BloodPressure;
use App\CLH\CCD\Importer\StorageStrategies\Biometrics\Weight;
use App\Models\CCD\Ccda;
use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdInsurancePolicy;
use App\Models\CCD\CcdMedication;
use App\Models\CCD\CcdProblem;
use App\Models\CPM\CpmMisc;
use App\PatientCareTeamMember;
use App\PatientContactWindow;
use App\PatientInfo;
use App\PhoneNumber;
use App\User;

class ImportManager
{
    private $allergiesImport;
    private $ccda;
    private $demographicsImport;
    private $medicationsImport;
    private $problemsImport;
    private $ccdaStrategies;
    private $user;
    private $decodedCcda;

    public function __construct(array $allergiesImport = null,
                                DemographicsImport $demographicsImport,
                                array $medicationsImport,
                                array $problemsImport,
                                array $strategies,
                                User $user,
                                Ccda $ccda)
    {
        $this->allergiesImport = $allergiesImport;
        $this->demographicsImport = $demographicsImport;
        $this->medicationsImport = $medicationsImport;
        $this->problemsImport = $problemsImport;
        $this->ccdaStrategies = $strategies;
        $this->user = $user;
        $this->ccda = $ccda;
        $this->decodedCcda = \GuzzleHttp\json_decode($ccda->json);
    }

    public function import()
    {
        $strategies = \Config::get('ccdimporterstrategiesmaps');

        /**
         * Allergies List
         */
        $this->storeAllergies($strategies['storage'][0]);


        /**
         * Problems List
         */
        //this gets the storage strategy from config/ccdimportersections by sectionId
        $this->storeProblemsList($strategies['storage'][2]);


        /**
         * Problems To Monitor
         */
        //this gets the storage strategy from config/ccdimportersections by sectionId
        $this->storeProblemsToMonitor($strategies['storage'][3]);


        /**
         * Medications List
         */
        //this gets the storage strategy from config/ccdimportersections by sectionId
        $this->storeMedications($strategies['storage'][1]);


        /**
         * The following Sections are the same for each CCD
         */
        $providerId = empty($this->demographicsImport->provider_id) ? null : $this->demographicsImport->provider_id;

        if ($providerId) {
            //care team
            $member = PatientCareTeamMember::create([
                'user_id'        => $this->user->id,
                'member_user_id' => $providerId,
                'type'           => PatientCareTeamMember::MEMBER,
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


        //patient info
        $patientInfo = PatientInfo::updateOrCreate([
            'user_id' => $this->user->id,
        ], [
            'ccda_id'                    => $this->ccda->id,
            'birth_date'                 => $this->demographicsImport->dob,
            'careplan_status'            => 'draft',
            'ccm_status'                 => 'enrolled',
            'consent_date'               => $this->demographicsImport->consent_date,
            'gender'                     => $this->demographicsImport->gender,
            'mrn_number'                 => $this->demographicsImport->mrn_number,
            'preferred_contact_language' => $this->demographicsImport->preferred_contact_language,
            'preferred_contact_location' => $this->demographicsImport->location_id,
            'preferred_contact_method'   => 'CCT',
            'user_id'                    => $this->user->id,
        ]);

        // update timezone
        $this->user->timezone = 'America/New_York';

        PatientContactWindow::sync($patientInfo, [1,2,3,4,5]);


        if (empty($patientInfo)) {
            throw new \Exception('Unable to create patient info');
        }

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
        if (!empty($weight)) $weightParseAndStore->import($weight);

        //Blood Pressure
        $bloodPressureParseAndStore = new BloodPressure($this->user->program_id, $this->user);
        $bloodPressure = $bloodPressureParseAndStore->parse($this->decodedCcda);
        if (!empty($bloodPressure)) $bloodPressureParseAndStore->import($bloodPressure);


        //Insurance
        $insurance = CcdInsurancePolicy::where('ccda_id', '=', $this->ccda->id)
            ->update([
                    'patient_id' => $this->user->id,
                ]
            );

        return true;
    }

    private function storeAllergies($allergiesListStorage)
    {
        if (empty($this->allergiesImport)) return false;

        foreach ($this->allergiesImport as $allergy) {

            $ccdAllergy = CcdAllergy::create([
                'ccda_id'            => $allergy->ccda_id,
                'vendor_id'          => $allergy->vendor_id,
                'patient_id'         => $this->user->id,
                'ccd_allergy_log_id' => $allergy->ccd_allergy_log_id,
                'allergen_name'      => $allergy->allergen_name,
            ]);
        }

        $misc = CpmMisc::whereName(CpmMisc::ALLERGIES)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id);
    }

    private function storeProblemsList($problemsListStorage)
    {
        if (empty($this->problemsImport)) return false;

        foreach ($this->problemsImport as $problem) {
            $ccdProblem = CcdProblem::create([
                'ccda_id'            => $problem->ccda_id,
                'vendor_id'          => $problem->vendor_id,
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
    }

    private function storeProblemsToMonitor($problemsToMonitorStorage)
    {
        if (empty($this->problemsImport)) return false;

        if (class_exists($problemsToMonitorStorage)) {
            $storage = new $problemsToMonitorStorage($this->user->program_id, $this->user);

            $problemsToActivate = [];

            foreach ($this->problemsImport as $problem) {
                if (empty($problem->cpm_problem_id)) continue;

                $problemsToActivate[] = $problem->cpm_problem_id;
            }

            $storage->import(array_unique($problemsToActivate));
        }
    }

    private function storeMedications($medicationsListStorage)
    {
        if (empty($this->medicationsImport)) return false;

        foreach ($this->medicationsImport as $medication) {
            $ccdMedication = CcdMedication::create([
                'ccda_id'               => $medication->ccda_id,
                'vendor_id'             => $medication->vendor_id,
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
    }
}