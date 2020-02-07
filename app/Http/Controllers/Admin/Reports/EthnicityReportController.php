<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin\Reports;

use CircleLinkHealth\Core\Exports\FromArray;
use App\Http\Controllers\Controller;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsLog;
use App\Models\CCD\CcdVendor;
use CircleLinkHealth\Customer\Entities\Practice;

class EthnicityReportController extends Controller
{
    public function getReport()
    {
        $data = DemographicsLog::all();

        if ($data->isEmpty()) {
            return 'No Data found.';
        }
        //Prepare spreadsheet data
        $filtered = $data->map(
            function ($demoLog) {
                $ccdVendor = CcdVendor::find($demoLog->vendor_id);
                if ($ccdVendor) {
                    $program = Practice::find($ccdVendor->program_id);

                    if ($program) {
                        return [
                            'program'   => $program->display_name,
                            'ethnicity' => $demoLog->ethnicity,
                            'race'      => $demoLog->race,
                        ];
                    }
                    Log::error("Ccd Vendor with id: {$ccdVendor->id} has invalid program_id. Practice with id:{$ccdVendor->program_id} not found. ");

                    return null;
                }
                Log::error("Ccd Demographics Log with id: {$demoLog->id} has invalid vendor_id. CcdVendor with id:{$demoLog->vendor_id} not found. ");

                return null;
            }
        )->filter();

        if ($filtered->isEmpty()) {
            return 'No Valid Data found.';
        }

        $filename = 'Ethnicity Report';

        return (new FromArray($filename, $filtered->all()))->download($filename);
    }
}
