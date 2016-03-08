<?php

namespace App\CLH\CCD\ItemLogger;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\ParsingStrategies\Facades\UserMetaParserHelpers;

class CcdItemLogger
{
    protected $ccd;
    protected $vendorId;

    protected $transformer;

    protected $foreignKeys = [];


    public function __construct(Ccda $ccd)
    {
        $this->ccd = json_decode( $ccd->json );
        $this->ccdaId = $ccd->id;
        $this->vendorId = $ccd->vendor_id;

        $this->foreignKeys = [
            'ccda_id' => $this->ccdaId,
            'vendor_id' => $this->vendorId,
        ];

        $this->transformer = new CcdToLogTranformer();
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
            $saved = ( new CcdAllergyLog() )->create(
                array_merge($this->transformer->allergy( $allergy ), $this->foreignKeys)
            );
        }

        return true;
    }

    public function logDemographics()
    {
        $demographics = $this->ccd->demographics;

        $saved = ( new CcdDemographicsLog() )->create(
            array_merge($this->transformer->demographics($demographics), $this->foreignKeys)
        );

        return true;
    }

    public function logDocument()
    {
        $document = $this->ccd->document;

        $saved = ( new CcdDocumentLog() )->create(
            array_merge($this->transformer->document($document), $this->foreignKeys)
        );

        return true;
    }

    public function logMedications()
    {
        $medications = $this->ccd->medications;

        foreach ( $medications as $med ) {
            $saved = ( new CcdMedicationLog() )->create(
                array_merge($this->transformer->medication($med), $this->foreignKeys)
            );
        }

        return true;
    }

    public function logProblems()
    {
        $problems = $this->ccd->problems;

        foreach ( $problems as $prob ) {
            $saved = ( new CcdProblemLog() )->create(
                array_merge($this->transformer->problem($prob), $this->foreignKeys)
            );

        }
    }

    public function logProvider()
    {
        //Add them both together
        array_push( $this->ccd->document->documentation_of, $this->ccd->document->author );

        $providers = $this->ccd->document->documentation_of;

        foreach ( $providers as $provider ) {
            $saved = ( new CcdProviderLog() )->create(
                array_merge($this->transformer->provider($provider), $this->foreignKeys)
            );
        }

        return true;
    }

}