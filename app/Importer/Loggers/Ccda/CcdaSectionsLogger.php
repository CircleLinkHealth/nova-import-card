<?php

namespace App\Importer\Loggers\Ccda;


use App\CLH\CCD\ItemLogger\AllergyLog;
use App\CLH\CCD\ItemLogger\DemographicsLog;
use App\CLH\CCD\ItemLogger\DocumentLog;
use App\CLH\CCD\ItemLogger\MedicationLog;
use App\CLH\CCD\ItemLogger\ProblemLog;
use App\CLH\CCD\ItemLogger\ProviderLog;
use App\Contracts\Importer\HealthRecord\HealthRecordLogger;
use App\Models\CCD\Ccda;

class CcdaSectionsLogger implements HealthRecordLogger
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

    /**
     * Transform the Demographics Section into Log models..
     * @return HealthRecordLogger
     */
    public function logDemographicsSection() : HealthRecordLogger
    {
        $demographics = $this->ccd->demographics;

        $saved = DemographicsLog::create(
            array_merge($this->transformer->demographics($demographics), $this->foreignKeys)
        );

        return $this;
    }

    /**
     * Transform the Document Section into Log models..
     * @return HealthRecordLogger
     */
    public function logDocumentSection() : HealthRecordLogger
    {
        $document = $this->ccd->document;

        $saved = DocumentLog::create(
            array_merge($this->transformer->document($document), $this->foreignKeys)
        );

        return $this;
    }

    /**
     * Transform the Medications Section into Log models..
     * @return HealthRecordLogger
     */
    public function logMedicationsSection() : HealthRecordLogger
    {
        $medications = $this->ccd->medications;

        foreach ($medications as $med) {
            $saved = MedicationLog::create(
                array_merge($this->transformer->medication($med), $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Transform the Problems Section into Log models..
     * @return HealthRecordLogger
     */
    public function logProblemsSection() : HealthRecordLogger
    {
        $problems = $this->ccd->problems;

        foreach ($problems as $prob) {
            $saved = ProblemLog::create(
                array_merge($this->transformer->problem($prob), $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Transform the Providers Section into Log models..
     * @return HealthRecordLogger
     */
    public function logProvidersSection() : HealthRecordLogger
    {
        //Add them both together
        array_push($this->ccd->document->documentation_of, $this->ccd->document->author);

        array_push($this->ccd->document->documentation_of, $this->ccd->demographics->provider);

        $providers = $this->ccd->document->documentation_of;

        foreach ($providers as $provider) {
            $saved = ProviderLog::create(
                array_merge($this->transformer->provider($provider), $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log all Sections.
     * @return bool
     */
    public function logAllSections() : bool
    {
        $this->logAllergiesSection()
            ->logDemographicsSection()
            ->logDocumentSection()
            ->logMedicationsSection()
            ->logProblemsSection()
            ->logProvidersSection();
    }

    /**
     * Transform the Allergies Section into Log models..
     * @return HealthRecordLogger
     */
    public function logAllergiesSection() : HealthRecordLogger
    {
        $allergies = $this->ccd->allergies;

        foreach ($allergies as $allergy) {
            $saved = AllergyLog::create(
                array_merge($this->transformer->allergy($allergy), $this->foreignKeys)
            );
        }

        return $this;
    }
}