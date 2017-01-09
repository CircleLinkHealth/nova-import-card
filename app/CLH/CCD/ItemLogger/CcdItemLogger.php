<?php

namespace App\CLH\CCD\ItemLogger;


use App\Models\CCD\Ccda;

class CcdItemLogger
{
    protected $ccd;
    protected $vendorId;

    protected $transformer;

    protected $foreignKeys = [];


    public function __construct(Ccda $ccd)
    {
        $this->ccd = json_decode($ccd->json);
        $this->ccdaId = $ccd->id;
        $this->vendorId = $ccd->vendor_id;

        $this->foreignKeys = [
            'ccda_id'   => $this->ccdaId,
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

        foreach ($allergies as $allergy) {
            $saved = CcdAllergyLog::create(
                array_merge($this->transformer->allergy($allergy), $this->foreignKeys)
            );
        }

        return true;
    }

    public function logDemographics()
    {
        $demographics = $this->ccd->demographics;

        $saved = CcdDemographicsLog::create(
            array_merge($this->transformer->demographics($demographics), $this->foreignKeys)
        );

        return true;
    }

    public function logDocument()
    {
        $document = $this->ccd->document;

        $saved = CcdDocumentLog::create(
            array_merge($this->transformer->document($document), $this->foreignKeys)
        );

        return true;
    }

    public function logMedications()
    {
        $medications = $this->ccd->medications;

        foreach ($medications as $med) {
            $saved = CcdMedicationLog::create(
                array_merge($this->transformer->medication($med), $this->foreignKeys)
            );
        }

        return true;
    }

    public function logProblems()
    {
        $problems = $this->ccd->problems;

        foreach ($problems as $prob) {
            $saved = CcdProblemLog::create(
                array_merge($this->transformer->problem($prob), $this->foreignKeys)
            );

        }
    }

    public function logProvider()
    {
        //Add them both together
        array_push($this->ccd->document->documentation_of, $this->ccd->document->author);

        array_push($this->ccd->document->documentation_of, $this->ccd->demographics->provider);

        $providers = $this->ccd->document->documentation_of;

        foreach ($providers as $provider) {
            $saved = CcdProviderLog::create(
                array_merge($this->transformer->provider($provider), $this->foreignKeys)
            );
        }

        return true;
    }

}