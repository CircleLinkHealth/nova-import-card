<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\Importer\ParsingStrategies\CareTeam\PrimaryProviders as PrimaryProvidersParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserConfig as UserConfigParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserMeta as UserMetaParser;
use App\CLH\CCD\Importer\ParsingStrategies\Location\ProviderLocation as ProviderLocationParser;
use App\CLH\CCD\Importer\ParsingStrategies\Problems\DetectsProblemCodeSystemTrait;
use App\CLH\CCD\ImportRoutine\ExecutesImportRoutine;
use App\CLH\CCD\ImportRoutine\RoutineBuilder;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\CLH\Repositories\UserRepository;
use App\Location;
use App\Models\CCD\Ccda;
use App\Models\CCD\CcdInsurancePolicy;
use App\Models\CCD\CcdVendor;
use App\Models\CCD\ValidatesQAImportOutput;
use App\User;

class QAImportManager
{
    use DetectsProblemCodeSystemTrait;
    use ExecutesImportRoutine;
    use ValidatesQAImportOutput;

    private $blogId;
    private $ccd;
    private $routine;
    //If the CCD came from the API (ie. Aprima), we have the location already
    private $locationId;
    //If the CCD came from the API (ie. Aprima), they sent us the provider already
    private $providerId;

    public function __construct($blogId, Ccda $ccda, $providerId = false, $locationId = false)
    {
        $this->blogId = $blogId;
        $this->ccda = $ccda;
        $this->ccd = json_decode($ccda->json);
        $this->locationId = $locationId;
        $this->providerId = $providerId;

        $this->routine = empty($ccda->vendor_id)
            ? (new RoutineBuilder($this->ccd))->getRoutine()
            : CcdVendor::find($ccda->vendor_id)->routine()->first()->strategies()->get();
    }

    public function generateCarePlanFromCCD()
    {
        $strategies = \Config::get('ccdimporterstrategiesmaps');

        $output = [];

        /**
         * Temporary fix for Middletown CCDs
         * They say problem codes are ICD-10, but they are ICD-9
         */
        if ($this->ccda->vendor_id == 9) {
            foreach ($this->ccd->problems as $problem) {
                if ($problem->code_system == '2.16.840.1.113883.6.4') {
                    $problem->code_system = '2.16.840.1.113883.6.103';
                    $problem->code_system_name = 'ICD-9';
                }
            }
        }

        /**
         * Parse and Import Allergies List, Medications List, Problems List, Problems To Monitor
         */
        foreach ($this->routine as $routine) {
            $validator = new $strategies['validation'][$routine->validator_id]();
            $parser = new $strategies['parsing'][$routine->parser_id]();
            $output[$routine->importer_section_id] = $parser->parse($this->ccda, $validator);
        }

        /**
         * The following Sections are the same for each CCD
         */

        $demographics = DemographicsImport::firstOrNew(['ccda_id' => $this->ccda->id]);
        $demographics->vendor_id = $this->ccda->vendor_id;
        $demographics->program_id = $this->blogId;

        /**
         * Parse and Import User Meta
         */
        $userMetaParser = new UserMetaParser(new UserMetaTemplate());
        $userMeta = $userMetaParser->parse($this->ccda);
        $output['userMeta'] = $userMeta;

        $demographics->first_name = ucwords(strtolower($userMeta->first_name));
        $demographics->last_name = ucwords(strtolower($userMeta->last_name));

        /**
         * Parse Provider Location
         */
        if (!$this->locationId) {
            $locationParser = new ProviderLocationParser();
            $locations = $locationParser->parse($this->ccda);
            $output['location'] = $locations;

            $demographics->location_id = isset($locations[0]) ? $locations[0]->id : null;
        }

        //If a locationId was passed in (case of Aprima Api)
        if ($this->locationId) {
            //Kinda hacky. Refactor here soon
            $output['location'][0] = Location::find($this->locationId);
            $demographics->location_id = $this->locationId;
        }

        /**
         * Parse provider (Lead Contact and Billing)
         */
        if (!$this->providerId) {
            $primaryProviderParser = new PrimaryProvidersParser(new UserRepository(), $this->locationId);
            //we well get back user objects (providers)
            $users = $primaryProviderParser->parse($this->ccda);
            $output['provider'] = $users;

            $demographics->provider_id = isset($users[0])
                ? $users[0]->id
                : null;
        }

        //If a providerId was passed in (case of Aprima Api)
        if ($this->providerId) {
            //Kinda hacky. Refactor here soon
            $output['provider'][0] = User::find($this->providerId);
            $demographics->provider_id = $this->providerId;
        }

        /**
         * Parse and Import User Config
         */
        $userConfigParser = new UserConfigParser(new UserConfigTemplate(), $this->blogId);
        $userConfig = $userConfigParser->parse($this->ccda);
        $output['userConfig'] = $userConfig;

        $demographics->dob = $userConfig->birth_date;
        $demographics->gender = $userConfig->gender;
        $demographics->mrn_number = $userConfig->mrn_number;
        $demographics->street = $userConfig->address;
        $demographics->city = $userConfig->city;
        $demographics->state = $userConfig->state;
        $demographics->zip = $userConfig->zip;
        $demographics->cell_phone = $userConfig->mobile_phone_number;
        $demographics->home_phone = $userConfig->home_phone_number;
        $demographics->work_phone = $userConfig->work_phone_number;
        $demographics->email = $userConfig->email;
        $demographics->study_phone_number = $userConfig->study_phone_number;
        $demographics->preferred_contact_language = $userConfig->preferred_contact_language;
        $demographics->consent_date = $userConfig->consent_date;
        $demographics->save();


        if (!empty($this->ccd->payers)) {
            foreach ($this->ccd->payers as $payer) {

                if (empty($payer->insurance)) continue;
                
                $insurance = CcdInsurancePolicy::create([
                    'ccda_id' => $this->ccda->id,
                    'patient_id' => null,
                    'name' => $payer->insurance,
                    'type' => $payer->policy_type,
                    'policy_id' => $payer->policy_id,
                    'relation' => $payer->relation,
                    'subscriber' => $payer->subscriber,
                    'approved' => false,
                ]);
            }
        }

        return $this->validateQAImportOutput($this->ccda, $output);
    }
}