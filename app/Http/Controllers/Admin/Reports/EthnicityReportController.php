<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin\Reports;

use App\Exports\FromArray;
use App\Http\Controllers\Controller;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Models\CCD\CcdVendor;
use CircleLinkHealth\Customer\Entities\Practice;

class EthnicityReportController extends Controller
{
    public function getReport()
    {
        $data = DemographicsLog::all();

        //Prepare spreadsheet data
        $filtered = $data->map(
            function ($demoLog) {
                $ccdVendor = CcdVendor::find($demoLog->vendor_id);
                $program = Practice::find($ccdVendor->program_id);

                return [
                    'program'   => $program->display_name,
                    'ethnicity' => $demoLog->ethnicity,
                    'race'      => $demoLog->race,
                ];
            }
        );

        $filename = 'Ethnicity Report';

        return (new FromArray($filename, $filtered->all()))->download($filename);
    }
}
