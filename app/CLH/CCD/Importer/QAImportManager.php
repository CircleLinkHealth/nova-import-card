<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\Importer\ParsingStrategies\Problems\DetectsProblemCodeSystemTrait;
use App\CLH\CCD\ImportRoutine\ExecutesImportRoutine;
use App\CLH\CCD\ImportRoutine\RoutineBuilder;
use App\Models\CCD\CcdInsurancePolicy;
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
         * The following Sections are the same for each CCD
         */




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