<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers;

use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\PhoneNumber;

class CcdToLogTranformer
{
    /**
     * @return array
     */
    public function allergy(object $allergy)
    {
        return [
            'start'         => $allergy->date_range->start,
            'end'           => $allergy->date_range->end,
            'status'        => $allergy->status,
            'allergen_name' => $allergy->allergen->name,
        ];
    }

    /**
     * @param $demographics
     *
     * @return array
     */
    public function demographics($demographics)
    {
        $phones = $this->getAllPhoneNumbers($demographics->phones);

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
            'work_phone'    => $phones['alternate'][0],
            'primary_phone' => $phones['primary'][0],
            'email'         => $demographics->email,
            'language'      => $demographics->language,
            'ethnicity'     => $demographics->ethnicity,
        ];
    }

    /**
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
     * Returns formatted phone numbers, organized by type ('home', 'mobile' etc).
     *
     * @param array $phones
     *
     * @return array
     */
    public function getAllPhoneNumbers($phones = [])
    {
        $home    = [];
        $mobile  = [];
        $work    = [];
        $primary = [];

        foreach ($phones as $phone) {
            if ( ! isset($phone->number)) {
                continue;
            }

            $type = isset($phone->type)
                ? $phone->type
                : 'home';

            if ( ! $number = (new StringManipulation())->formatPhoneNumber($phone->number)) {
                continue;
            }

            switch ($type) {
                case PhoneNumber::HOME:
                    array_push($home, $number);
                    break;
                case PhoneNumber::MOBILE:
                    array_push($mobile, $number);
                    break;
                case PhoneNumber::ALTERNATE:
                    array_push($work, $number);
                    break;
                case 'primary_phone':
                    array_push($primary, $number);
                    break;
            }
        }

        $phoneCollections = compact(PhoneNumber::HOME, PhoneNumber::MOBILE, PhoneNumber::ALTERNATE, 'primary');

        foreach ($phoneCollections as $key => $phoneCollection) {
            if (empty($phoneCollection)) {
                array_push($phoneCollections[$key], null);
            }
        }

        return $phoneCollections;
    }

    /**
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
            if (empty($translation)) {
                continue;
            }
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
        $phones = $this->getAllPhoneNumbers($provider->phones);

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
            'work_phone' => $phones['alternate'][0],
        ];
    }
}
