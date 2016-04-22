<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\Importer\StorageStrategies\DefaultSections\TransitionalCare;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserConfig as UserConfigStorage;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserMeta as UserMetaStorage;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserConfig as UserConfigParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserMeta as UserMetaParser;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
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

        /**
         * @todo: trash this
         * Parse and Import User Meta
         */
//        $userMetaTemplate = new UserMetaTemplate();
//        $userMetaTemplate->first_name = $this->demographicsImport->first_name;
//        $userMetaTemplate->last_name = $this->demographicsImport->last_name;
//        ( new UserMetaStorage( $this->user->program_id, $this->user ) )->import( $userMetaTemplate->getArray() );

        /**
         * Import User Config
         */
        $userConfigTemplate = new UserConfigTemplate();

        $providerId = empty($this->demographicsImport->provider_id) ? null : $this->demographicsImport->provider_id;

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


        $patientInfo = PatientInfo::create([
            'birth_date' => $this->demographicsImport->dob,
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

        if (!empty($homeNumber = $this->demographicsImport->home_phone)) {
            $homePhone = PhoneNumber::create([
                'user_id' => $this->user,
                'number' => $homeNumber,
                'type' => PhoneNumber::HOME,
            ]);
        }

        if (!empty($mobileNumber = $this->demographicsImport->cell_phone)) {
            $mobilePhone = PhoneNumber::create([
                'user_id' => $this->user,
                'number' => $mobileNumber,
                'type' => PhoneNumber::MOBILE,
            ]);
        }

        if (!empty($workNumber = $this->demographicsImport->work_phone)) {
            $workPhone = PhoneNumber::create([
                'user_id' => $this->user,
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


//        $userConfigTemplate->preferred_contact_location = $this->demographicsImport->location_id;
//        $userConfigTemplate->mrn_number = $this->demographicsImport->mrn_number;
//        $userConfigTemplate->study_phone_number = empty($this->demographicsImport->cell_phone)
//            ? empty($this->demographicsImport->home_phone)
//                ? $this->demographicsImport->work_phone
//                : $this->demographicsImport->home_phone
//            : $this->demographicsImport->cell_phone;
//        $userConfigTemplate->home_phone_number = $this->demographicsImport->home_phone;
//        $userConfigTemplate->mobile_phone_number = $this->demographicsImport->cell_phone;
//        $userConfigTemplate->work_phone_number = $this->demographicsImport->work_phone;
//        $userConfigTemplate->birth_date = $this->demographicsImport->dob;
//        $userConfigTemplate->consent_date = $this->demographicsImport->consent_date;

//        $userConfigTemplate->care_team = $providerId;
//        $userConfigTemplate->email = $this->demographicsImport->email;
//        $userConfigTemplate->lead_contact = $providerId;
//        $userConfigTemplate->billing_provider = $providerId;
//        $userConfigTemplate->gender = $this->demographicsImport->gender;
//        $userConfigTemplate->address = $this->demographicsImport->street;
//        $userConfigTemplate->city = $this->demographicsImport->city;
//        $userConfigTemplate->state = $this->demographicsImport->state;
//        $userConfigTemplate->zip = $this->demographicsImport->zip;

        /**
         * Persist UserConfig
         */
        $userConfigParser = new UserConfigParser($userConfigTemplate, $this->user->program_id);
        (new UserConfigStorage($this->user->program_id, $this->user))->import($userConfigTemplate->getArray());

        /**
         * CarePlan Defaults
         */
        $transitionalCare = new TransitionalCare($this->user->program_id, $this->user);
        $transitionalCare->setDefaults();

        return true;
    }

    private function storeAllergies($allergiesListStorage)
    {
        if (empty($this->allergiesImport)) return false;

        if (class_exists($allergiesListStorage)) {
            $storage = new $allergiesListStorage($this->user->program_id, $this->user);

            $allergiesList = '';

            foreach ($this->allergiesImport as $allergy) {
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

            }


            $storage->import($medicationsList);
        }
    }
}