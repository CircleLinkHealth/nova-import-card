<?php

namespace App\CLH\CCD\ItemLogger;


use App\CLH\CCD\Importer\ParsingStrategies\Facades\UserMetaParserHelpers;

class CcdToLogTranformer
{
    public function allergy($allergy)
    {
        return [
            'start' => $allergy->date_range->start,
            'end' => $allergy->date_range->end,
            'status' => $allergy->status,
            'allergen_name' => $allergy->allergen->name,
        ];
    }

    public function demographics($demographics)
    {
        $phones = UserMetaParserHelpers::getAllPhoneNumbers( $demographics->phones );

        return [
            'first_name' => array_key_exists( 0, $demographics->name->given ) ? $demographics->name->given[ 0 ] : null,
            'last_name' => $demographics->name->family,
            'dob' => $demographics->dob,
            'gender' => $demographics->gender,
            'mrn_number' => $demographics->mrn_number,
            'street' => array_key_exists( 0, $demographics->address->street ) ? $demographics->address->street[ 0 ] : null,
            'street2' => array_key_exists( 1, $demographics->address->street ) ? $demographics->address->street[ 1 ] : null,
            'city' => $demographics->address->city,
            'state' => $demographics->address->state,
            'zip' => $demographics->address->zip,
            'cell_phone' => $phones[ 'mobile' ][ 0 ],
            'home_phone' => $phones[ 'home' ][ 0 ],
            'work_phone' => $phones[ 'work' ][ 0 ],
            'email' => $demographics->email,
            'language' => $demographics->language,
        ];
    }

    public function document($document)
    {
        return [
            'custodian' => empty($document->custodian->name) ?: trim( $document->custodian->name ),
            'type' => $document->type,
        ];
    }

    public function medication($medication)
    {
        return [
            'reference' => $medication->reference,
            'reference_title' => $medication->reference_title,
            'reference_sig' => $medication->reference_sig,
            'start' => $medication->date_range->start,
            'end' => $medication->date_range->end,
            'status' => $medication->status,
            'text' => $medication->text,
            'product_name' => $medication->product->name,
            'product_code' => $medication->product->code,
            'product_code_system' => $medication->product->code_system,
            'product_text' => $medication->product->text,
            'translation_name' => $medication->product->translation->name,
            'translation_code' => $medication->product->translation->code,
            'translation_code_system' => $medication->product->translation->code_system,
            'translation_code_system_name' => $medication->product->translation->code_system_name,
        ];
    }

    public function problem($problem)
    {
        return [
            'reference' => $problem->reference,
            'reference_title' => trim($problem->reference_title),
            'start' => $problem->date_range->start,
            'end' => $problem->date_range->end,
            'status' => $problem->status,
            'name' => $problem->name,
            'code' => $problem->code,
            'code_system' => $problem->code_system,
            'code_system_name' => $problem->code_system_name,
            'translation_name' => $problem->translation->name,
            'translation_code' => $problem->translation->code,
            'translation_code_system' => $problem->translation->code_system,
            'translation_code_system_name' => $problem->translation->code_system_name,
        ];
    }

    public function provider($provider)
    {
        $phones = UserMetaParserHelpers::getAllPhoneNumbers( $provider->phones );

        return [
            'npi' => isset($provider->npi) ? $provider->npi : null,
            'first_name' => isset($provider->name->given) && array_key_exists( 0, $provider->name->given ) ? $provider->name->given[ 0 ] : null,
            'last_name' => isset($provider->name->family) ? $provider->name->family : null,
            'organization' => isset($provider->organization) ? $provider->organization : null,
            'street' => array_key_exists( 0, $provider->address->street ) ? $provider->address->street[ 0 ] : null,
            'city' => $provider->address->city,
            'state' => $provider->address->state,
            'zip' => $provider->address->zip,
            'cell_phone' => $phones[ 'mobile' ][ 0 ],
            'home_phone' => $phones[ 'home' ][ 0 ],
            'work_phone' => $phones[ 'work' ][ 0 ],
        ];
    }

}