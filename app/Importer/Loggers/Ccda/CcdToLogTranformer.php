<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Ccda;

use App\CLH\CCD\Importer\ParsingStrategies\Helpers\UserMetaParserHelpers;
use CircleLinkHealth\CarePlanModels\Entities\AllergyLog;
use App\Importer\Models\ItemLogs\DemographicsLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;

/**
 * Takes data from the the json CCD and transforms it so that it can be saved as one of the transformer Models.
 * There is a method in this class for each model, it is listed using @see above every function
 * Class CcdToLogTranformer.
 */
class CcdToLogTranformer
{
    /**
     * @see \CircleLinkHealth\CarePlanModels\Entities\AllergyLog
     *
     * @param $allergy
     *
     * @return array
     */
    public function allergy($allergy)
    {
        return [
            'start'         => $allergy->date_range->start,
            'end'           => $allergy->date_range->end,
            'status'        => $allergy->status,
            'allergen_name' => $allergy->allergen->name,
        ];
    }

    /**
     * @see DemographicsLog
     *
     * @param $demographics
     *
     * @return array
     */
    public function demographics($demographics)
    {
        $phones = (new UserMetaParserHelpers())->getAllPhoneNumbers($demographics->phones);

        return [
            'first_name' => array_key_exists(0, $demographics->name->given)
                ? $demographics->name->given[0]
                : null,
            'last_name'  => $demographics->name->family,
            'dob'        => $demographics->dob,
            'gender'     => $demographics->gender,
            'mrn_number' => $demographics->mrn_number,
            'street'     => array_key_exists(0, $demographics->address->street)
                ? $demographics->address->street[0]
                : null,
            'street2' => array_key_exists(1, $demographics->address->street)
                ? $demographics->address->street[1]
                : null,
            'city'          => $demographics->address->city,
            'state'         => $demographics->address->state,
            'zip'           => $demographics->address->zip,
            'cell_phone'    => $phones['mobile'][0],
            'home_phone'    => $phones['home'][0],
            'work_phone'    => $phones['work'][0],
            'primary_phone' => $phones['primary'][0],
            'email'         => $demographics->email,
            'language'      => $demographics->language,
            'ethnicity'     => $demographics->ethnicity,
        ];
    }

    /**
     * @see DocumentLog
     *
     * @param $document
     *
     * @return array
     */
    public function document($document)
    {
        return [
            'custodian' => empty($document->custodian->name)
                ?: trim($document->custodian->name),
            'type' => empty($document->type)
                ?: $document->type,
        ];
    }

    /**
     * @see InsuranceLog
     *
     * @param $payer
     *
     * @return array
     */
    public function insurance($payer)
    {
        return [
            'name'       => $payer->insurance,
            'type'       => $payer->policy_type,
            'policy_id'  => $payer->policy_id,
            'relation'   => $payer->relation,
            'subscriber' => $payer->subscriber,
        ];
    }

    /**
     * @see @see CircleLinkHealth\CarePlanModels\Entities\MedicationLog
     *
     * @param $medication
     *
     * @return array
     */
    public function medication($medication)
    {
        return [
            'reference'                    => $medication->reference,
            'reference_title'              => $medication->reference_title,
            'reference_sig'                => $medication->reference_sig,
            'start'                        => $medication->date_range->start,
            'end'                          => $medication->date_range->end,
            'status'                       => $medication->status,
            'text'                         => $medication->text,
            'product_name'                 => $medication->product->name,
            'product_code'                 => $medication->product->code,
            'product_code_system'          => $medication->product->code_system,
            'product_text'                 => $medication->product->text,
            'translation_name'             => $medication->product->translation->name,
            'translation_code'             => $medication->product->translation->code,
            'translation_code_system'      => $medication->product->translation->code_system,
            'translation_code_system_name' => $medication->product->translation->code_system_name,
        ];
    }

    /**
     * @param $documentSection
     * @param $demographicsSection
     *
     * @return array
     */
    public function parseProviders($documentSection, $demographicsSection)
    {
        //Add them both together
        array_push($documentSection->documentation_of, $documentSection->author);

        array_push($documentSection->documentation_of, $demographicsSection->provider);

        $address         = new \stdClass();
        $address->street = [];
        $address->city   = '';
        $address->state  = '';
        $address->zip    = '';

        $legalAuth               = new \stdClass();
        $legalAuth->name         = $documentSection->legal_authenticator->assigned_person;
        $legalAuth->phones       = [];
        $legalAuth->npi          = '';
        $legalAuth->organization = '';
        $legalAuth->address      = $address;

        array_push($documentSection->documentation_of, $legalAuth);

        return $documentSection->documentation_of;
    }

    /**
     * @see ProblemLog
     *
     * @param $problem
     *
     * @return array
     */
    public function problem($problem)
    {
        return [
            'reference'                    => $problem->reference,
            'reference_title'              => trim($problem->reference_title),
            'start'                        => $problem->date_range->start,
            'end'                          => $problem->date_range->end,
            'status'                       => $problem->status,
            'name'                         => $problem->name,
            'code'                         => $problem->code,
            'code_system'                  => $problem->code_system,
            'code_system_name'             => $problem->code_system_name,
            'translation_name'             => $problem->translations[0]->name ?? '',
            'translation_code'             => $problem->translations[0]->code ?? '',
            'translation_code_system'      => $problem->translations[0]->code_system ?? '',
            'translation_code_system_name' => $problem->translations[0]->code_system_name ?? '',
        ];
    }

    public function problemCodes($ccdProblem)
    {
        $codes = [];

        if ( ! $ccdProblem->code_system_name) {
            $ccdProblem->code_system_name = getProblemCodeSystemName([$ccdProblem->code_system]);
        }

        if ($ccdProblem->code_system_name) {
            $codes[] = [
                'code_system_name' => $ccdProblem->code_system_name,
                'code_system_oid'  => $ccdProblem->code_system,
                'code'             => $ccdProblem->code,
                'name'             => $ccdProblem->name,
            ];
        }

        foreach ($ccdProblem->translations as $translation) {
            if ( ! $translation->code_system_name) {
                $translation->code_system_name = getProblemCodeSystemName([$translation->code_system]);

                if ( ! $translation->code_system_name) {
                    continue;
                }
            }

            $codes[] = [
                'code_system_name' => $translation->code_system_name,
                'code_system_oid'  => $translation->code_system,
                'code'             => $translation->code,
                'name'             => $translation->name,
            ];
        }

        return $codes;
    }

    /**
     * @see ProviderLog
     *
     * @param $provider
     *
     * @return array
     */
    public function provider($provider)
    {
        $phones = (new UserMetaParserHelpers())->getAllPhoneNumbers($provider->phones);

        return [
            'provider_id' => isset($provider->provider_id)
                ? $provider->provider_id
                : null,
            'npi' => isset($provider->npi)
                ? $provider->npi
                : null,
            'first_name' => isset($provider->name->given) && array_key_exists(0, $provider->name->given)
                ? $provider->name->given[0]
                : null,
            'last_name' => isset($provider->name->family)
                ? $provider->name->family
                : null,
            'organization' => isset($provider->organization)
                ? $provider->organization
                : null,
            'street' => array_key_exists(0, $provider->address->street)
                ? $provider->address->street[0]
                : null,
            'city'       => $provider->address->city,
            'state'      => $provider->address->state,
            'zip'        => $provider->address->zip,
            'cell_phone' => $phones['mobile'][0],
            'home_phone' => $phones['home'][0],
            'work_phone' => $phones['work'][0],
        ];
    }
}
