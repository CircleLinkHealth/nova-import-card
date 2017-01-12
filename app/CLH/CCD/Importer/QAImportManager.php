<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\Importer\ParsingStrategies\Problems\DetectsProblemCodeSystemTrait;
use App\CLH\CCD\ImportRoutine\ExecutesImportRoutine;
use App\CLH\CCD\ImportRoutine\RoutineBuilder;
use App\Models\CCD\CcdVendor;
use App\Models\CCD\ValidatesQAImportOutput;
use App\Models\MedicalRecords\Ccda;

class QAImportManager
{
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
         * The following Sections are the same for each CCD
         */






        return $this->validateQAImportOutput($this->ccda, $output);
    }
}