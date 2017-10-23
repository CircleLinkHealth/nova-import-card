<?php

namespace App\Importer\Loggers\Ccda;


use App\Facades\UserMetaParserHelpers;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\DemographicsLog;

/**
 * Takes data from the the json CCD and transforms it so that it can be saved as one of the transformer Models.
 * There is a method in this class for each model, it is listed using @see above every function
 * Class CcdToLogTranformer
 * @package App\CLH\CCD\ItemLogger
 */
class CcdToLogTranformer
{
    /**
     * @see AllergyLog
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
        $phones = UserMetaParserHelpers::getAllPhoneNumbers($demographics->phones);

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
            'street2'    => array_key_exists(1, $demographics->address->street)
                ? $demographics->address->street[1]
                : null,
            'city'       => $demographics->address->city,
            'state'      => $demographics->address->state,
            'zip'        => $demographics->address->zip,
            'cell_phone' => $phones['mobile'][0],
            'home_phone' => $phones['home'][0],
            'work_phone' => $phones['work'][0],
            'email'      => $demographics->email,
            'language'   => $demographics->language,
            'ethnicity'  => $demographics->ethnicity,
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
            'type'      => empty($document->type)
                ?: $document->type,
        ];
    }

    /**
     * @see @see App\Importer\Models\ItemLogs\MedicationLog
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
     * @see ProblemLog
     *
     * @param $problem
     *
     * @return array
     */
    public function problem($problem)
    {
        return [
            'reference'       => $problem->reference,
            'reference_title' => trim($problem->reference_title),
            'start'           => $problem->date_range->start,
            'end'             => $problem->date_range->end,
            'status'          => $problem->status,
            'name'            => $problem->name,
        ];
    }

    public function problemCodes($ccdProblem, $problemLog)
    {
        $codes[] = [
            'ccd_problem_log_id' => $problemLog->id,
            'code_system_name'   => $ccdProblem->code_system_name,
            'code_system_oid'    => $ccdProblem->code_system,
            'code'               => $ccdProblem->code,
        ];

        foreach ($ccdProblem->translations as $translation) {
            $codes[] = [
                'ccd_problem_log_id' => $problemLog->id,
                'code_system_name'   => $translation->code_system_name,
                'code_system_oid'    => $translation->code_system,
                'code'               => $translation->code,
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
        $phones = UserMetaParserHelpers::getAllPhoneNumbers($provider->phones);

        return [
            'provider_id'  => isset($provider->provider_id)
                ? $provider->provider_id
                : null,
            'npi'          => isset($provider->npi)
                ? $provider->npi
                : null,
            'first_name'   => isset($provider->name->given) && array_key_exists(0, $provider->name->given)
                ? $provider->name->given[0]
                : null,
            'last_name'    => isset($provider->name->family)
                ? $provider->name->family
                : null,
            'organization' => isset($provider->organization)
                ? $provider->organization
                : null,
            'street'       => array_key_exists(0, $provider->address->street)
                ? $provider->address->street[0]
                : null,
            'city'         => $provider->address->city,
            'state'        => $provider->address->state,
            'zip'          => $provider->address->zip,
            'cell_phone'   => $phones['mobile'][0],
            'home_phone'   => $phones['home'][0],
            'work_phone'   => $phones['work'][0],
        ];
    }

}