<?php

namespace App\CLH\CCD\ImportedItemsLogger;


use App\CLH\CCD\ImportedItemsLogger\CcdAllergyLog;
use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\ParsingStrategies\Facades\UserMetaParserHelpers;
use Illuminate\Support\Collection;

class CcdItemLogger
{
    protected $ccd;
    protected $vendorId;

    public function __construct(Ccda $ccd)
    {
        $this->ccd = json_decode( $ccd->json );
        $this->ccdaId = $ccd->id;
        $this->vendorId = $ccd->vendor_id;
    }

    public function logAll()
    {
        $this->logAllergies();
        $this->logDemographics();
        $this->logDocument();
        $this->logMedications();
        $this->logProblems();
        $this->logProvider();
    }

    public function logAllergies()
    {
        $allergies = $this->ccd->allergies;

        foreach ( $allergies as $allergy ) {
            $saved = ( new CcdAllergyLog() )->create( [
                'ccda_id' => $this->ccdaId,
                'vendor_id' => $this->vendorId,
                'start' => $allergy->date_range->start,
                'end' => $allergy->date_range->end,
                'status' => $allergy->status,
                'allergen_name' => $allergy->allergen->name,
            ] );
        }

        return true;
    }

    public function logDemographics()
    {
        $demographics = $this->ccd->demographics;

        $phones = UserMetaParserHelpers::getAllPhoneNumbers( $demographics->phones );

        $saved = ( new CcdDemographicsLog() )->create( [
            'ccda_id' => $this->ccdaId,
            'vendor_id' => $this->vendorId,
            'first_name' => array_key_exists( 0, $demographics->name->given ) ? $demographics->name->given[ 0 ] : null,
            'last_name' => $demographics->name->family,
            'dob' => $demographics->dob,
            'gender' => $demographics->gender,
            'mrn_number' => $demographics->mrn_number,
            'street' => array_key_exists( 0, $demographics->address->street ) ? $demographics->address->street[ 0 ] : null,
            'city' => $demographics->address->city,
            'state' => $demographics->address->state,
            'zip' => $demographics->address->zip,
            'cell_phone' => $phones[ 'mobile' ][ 0 ],
            'home_phone' => $phones[ 'home' ][ 0 ],
            'work_phone' => $phones[ 'work' ][ 0 ],
            'email' => $demographics->email,
        ] );

        return true;
    }

    public function logDocument()
    {
        $document = $this->ccd->document;

        $saved = ( new CcdDocumentLog() )->create( [
            'ccda_id' => $this->ccdaId,
            'vendor_id' => $this->vendorId,
            'custodian' => empty($document->custodian->name) ?: trim( $document->custodian->name ),
        ] );

        return true;
    }

    public function logMedications()
    {
        $medications = $this->ccd->medications;

        foreach ( $medications as $med ) {
            $saved = ( new CcdMedicationLog() )->create( [
                'ccda_id' => $this->ccdaId,
                'vendor_id' => $this->vendorId,
                'reference' => $med->reference,
                'reference_title' => $med->reference_title,
                'reference_sig' => $med->reference_sig,
                'start' => $med->date_range->start,
                'end' => $med->date_range->end,
                'status' => $med->status,
                'text' => $med->text,
                'product_name' => $med->product->name,
                'product_code' => $med->product->code,
                'product_code_system' => $med->product->code_system,
                'product_text' => $med->product->text,
                'translation_name' => $med->product->translation->name,
                'translation_code' => $med->product->translation->code,
                'translation_code_system' => $med->product->translation->code_system,
                'translation_code_system_name' => $med->product->translation->code_system_name,
            ] );
        }

        return true;
    }

    public function logProblems()
    {
        $problems = $this->ccd->problems;

        foreach ($problems as $prob)
        {
            $saved = ( new CcdProblemLog() )->create( [
                'ccda_id' => $this->ccdaId,
                'vendor_id' => $this->vendorId,
                'reference' => $prob->reference,
                'reference_title' => $prob->reference_title,
                'start' => $prob->date_range->start,
                'end' => $prob->date_range->end,
                'status' => $prob->status,
                'name' => $prob->name,
                'code' => $prob->code,
                'code_system' => $prob->code_system,
                'code_system_name' => $prob->code_system_name,
                'translation_name' => $prob->translation->name,
                'translation_code' => $prob->translation->code,
                'translation_code_system' => $prob->translation->code_system,
                'translation_code_system_name' => $prob->translation->code_system_name,
            ] );

        }
    }

    public function logProvider()
    {
        //Add them both together
        array_push($this->ccd->document->documentation_of, $this->ccd->document->author);

        $providers = $this->ccd->document->documentation_of;

        foreach($providers as $provider)
        {
            $phones = UserMetaParserHelpers::getAllPhoneNumbers( $provider->phones );

            $saved = (new CcdProviderLog())->create([
                'ccda_id' => $this->ccdaId,
                'vendor_id' => $this->vendorId,
                'npi' => isset($provider->npi) ?$provider->npi : null,
                'first_name' => array_key_exists( 0, $provider->name->given ) ? $provider->name->given[ 0 ] : null,
                'last_name' => $provider->name->family,
                'street' => array_key_exists( 0, $provider->address->street ) ? $provider->address->street[ 0 ] : null,
                'city' => $provider->address->city,
                'state' => $provider->address->state,
                'zip' => $provider->address->zip,
                'cell_phone' => $phones[ 'mobile' ][ 0 ],
                'home_phone' => $phones[ 'home' ][ 0 ],
                'work_phone' => $phones[ 'work' ][ 0 ],
            ]);
        }

        return true;
    }

}