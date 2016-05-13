<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\Importer\StorageStrategies\DefaultSections\TransitionalCare;
use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdMedication;
use App\Models\CCD\CcdProblem;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\PatientCareTeamMember;
use App\PatientInfo;
use App\PhoneNumber;
use App\User;

class ImportManager
{
    private $allergiesImport;
    private $demographicsImport;
    private $medicationsImport;
    private $problemsImport;
    private $ccdaStrategies;
    private $user;

    public function __construct(array $allergiesImport = null,
                                DemographicsImport $demographicsImport,
                                array $medicationsImport,
                                array $problemsImport,
                                array $strategies,
                                User $user)
    {
        $this->allergiesImport = $allergiesImport;
        $this->demographicsImport = $demographicsImport;
        $this->medicationsImport = $medicationsImport;
        $this->problemsImport = $problemsImport;
        $this->ccdaStrategies = $strategies;
        $this->user = $user;
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

        //care team
        $member = PatientCareTeamMember::create([
            'user_id' => $this->user->ID,
            'member_user_id' => $providerId,
            'type' => PatientCareTeamMember::MEMBER,
        ]);

        $billing = PatientCareTeamMember::create([
            'user_id' => $this->user->ID,
            'member_user_id' => $providerId,
            'type' => PatientCareTeamMember::BILLING_PROVIDER,
        ]);

        $lead = PatientCareTeamMember::create([
            'user_id' => $this->user->ID,
            'member_user_id' => $providerId,
            'type' => PatientCareTeamMember::LEAD_CONTACT,
        ]);

        //patient info
        $patientInfo = PatientInfo::updateOrCreate([
            'user_id' => $this->user->ID,
        ], [
            'birth_date' => $this->demographicsImport->dob,
            'careplan_status' => 'draft',
            'ccm_status' => 'enrolled',
            'consent_date' => $this->demographicsImport->consent_date,
            'gender' => $this->demographicsImport->gender,
            'mrn_number' => $this->demographicsImport->mrn_number,
            'preferred_cc_contact_days' => '2', //tuesday
            'preferred_contact_language' => $this->demographicsImport->preferred_contact_language,
            'preferred_contact_location' => $this->demographicsImport->location_id,
            'preferred_contact_method' => 'CCT',
            'preferred_contact_time' => '11:00 AM',
            'preferred_contact_timezone' => $this->demographicsImport->preferred_contact_timezone,
            'user_id' => $this->user->ID,
        ]);

        if (empty($patientInfo)) {
            throw new \Exception('Unable to create patient info');
        }

        if (!empty($homeNumber = $this->demographicsImport->home_phone)) {
            $homePhone = PhoneNumber::create([
                'user_id' => $this->user->ID,
                'number' => $homeNumber,
                'type' => PhoneNumber::HOME,
            ]);
        }

        if (!empty($mobileNumber = $this->demographicsImport->cell_phone)) {
            $mobilePhone = PhoneNumber::create([
                'user_id' => $this->user->ID,
                'number' => $mobileNumber,
                'type' => PhoneNumber::MOBILE,
            ]);
        }

        if (!empty($workNumber = $this->demographicsImport->work_phone)) {
            $workPhone = PhoneNumber::create([
                'user_id' => $this->user->ID,
                'number' => $workNumber,
                'type' => PhoneNumber::WORK,
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
         * CarePlan Defaults
         */
        $miscId = CpmMisc::whereName(CpmMisc::TRACK_CARE_TRANSITIONS)->first();

        $this->user->cpmMiscs()->attach($miscId->id);

        return true;
    }

    private function storeAllergies($allergiesListStorage)
    {
        if (empty($this->allergiesImport)) return false;

        if (class_exists($allergiesListStorage)) {
            $storage = new $allergiesListStorage($this->user->program_id, $this->user);

            $allergiesList = '';

            foreach ($this->allergiesImport as $allergy) {

                $ccdAllergy = CcdAllergy::create([
                    'ccda_id' => $allergy->ccda_id,
                    'vendor_id' => $allergy->vendor_id,
                    'patient_id' => $this->user->ID,
                    'ccd_allergy_log_id' => $allergy->ccd_allergy_log_id,
                    'allergen_name' => $allergy->allergen_name,
                ]);

                if (!isset($allergy->allergen_name)) continue;
                $allergiesList .= "\n\n";
                $allergiesList .= ucfirst(strtolower($allergy->allergen_name)) . ";";
            }

            $storage->import($allergiesList);
        }
    }

    private function storeProblemsList($problemsListStorage)
    {
        if (empty($this->problemsImport)) return false;

        if (class_exists($problemsListStorage)) {
            $storage = new $problemsListStorage($this->user->program_id, $this->user);

            $problemsList = '';

            foreach ($this->problemsImport as $problem) {
                $problemsList .= "\n\n";

                //quick fix to display snomed ct in middletown
                $codeSystemName = function ($problem) {
                    return empty($problem->code_system_name)
                        ? empty($problem->code_system)
                            ? null
                            : ($problem->code_system == '2.16.840.1.113883.6.96')
                                ? 'SNOMED CT'
                                : (($problem->code_system == '2.16.840.1.113883.6.4') || ($problem->code_system == '2.16.840.1.113883.6.103'))
                                    ? 'ICD-9'
                                    : ($problem->code_system == '2.16.840.1.113883.6.3')
                                        ? 'ICD-10'
                                        : null
                        : $problem->code_system_name;
                };

                $problemsList .= ucwords(strtolower($problem->name));

                $problemsList .= (is_null($codeSystemName($problem)))
                    ? '' : ', ' . strtoupper($codeSystemName($problem));

                $problemsList .= empty($problem->code) ? ';' : ', ' . $problem->code . ';';

                $ccdProblem = CcdProblem::create([
                    'ccda_id' => $problem->ccda_id,
                    'vendor_id' => $problem->vendor_id,
                    'ccd_problem_log_id' => $problem->ccd_problem_log_id,
                    'name' => $problem->name,
                    'code' => $problem->code,
                    'code_system' => $problem->code_system,
                    'code_system_name' => $problem->code_system_name,
                    'activate' => $problem->activate,
                    'cpm_problem_id' => $problem->cpm_problem_id,
                    'patient_id' => $this->user->ID,
                ]);
            }

            $storage->import($problemsList);
        }
    }

    private function storeProblemsToMonitor($problemsToMonitorStorage)
    {
        if (empty($this->problemsImport)) return false;

        if (class_exists($problemsToMonitorStorage)) {
            $storage = new $problemsToMonitorStorage($this->user->program_id, $this->user);

            $problemsToActivate = [];

            foreach ($this->problemsImport as $problem) {
                if (empty($problem->cpm_problem_id)) continue;

                $problemsToActivate[] = CPMProblem::find($problem->cpm_problem_id)->care_item_name;
            }

            $storage->import($problemsToActivate);
        }
    }

    private function storeMedications($medicationsListStorage)
    {
        if (empty($this->medicationsImport)) return false;

        if (class_exists($medicationsListStorage)) {
            $storage = new $medicationsListStorage($this->user->program_id, $this->user);

            $medicationsList = '';

            foreach ($this->medicationsImport as $medication) {
                $medicationsList .= "\n\n";
                empty($medication->name)
                    ?: $medicationsList .= ucfirst(strtolower($medication->name));

                $medicationsList .= ucfirst(
                    strtolower(
                        empty($medText = $medication->sig)
                            ? ';'
                            : ', ' . $medText . ";"
                    )
                );

                $ccdMedication = CcdMedication::create([
                    'ccda_id' => $medication->ccda_id,
                    'vendor_id' => $medication->vendor_id,
                    'ccd_medication_log_id' => $medication->ccd_medication_log_id,
                    'medication_group_id' => $medication->medication_group_id,
                    'name' => $medication->name,
                    'sig' => $medication->sig,
                    'code' => $medication->code,
                    'code_system' => $medication->code_system,
                    'code_system_name' => $medication->code_system_name,
                    'patient_id' => $this->user->ID,
                ]);
            }


            $storage->import($medicationsList);
        }
    }
}