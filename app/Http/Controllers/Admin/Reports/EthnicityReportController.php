<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Models\CCD\CcdVendor;
use App\Practice;
use Maatwebsite\Excel\Facades\Excel;

class EthnicityReportController extends Controller
{
    public function getReport()
    {
        $data = DemographicsLog::all();

        //Prepare spreadsheet data
        $filtered = $data->map(function ($demoLog) {
            $ccdVendor = CcdVendor::find($demoLog->vendor_id);
            $program = Practice::find($ccdVendor->program_id);

            return [
                'program' => $program->display_name,
                'ethnicity' => $demoLog->ethnicity,
                'race' => $demoLog->race,
            ];
        });

        Excel::create("Ethnicity Report", function ($excel) use ($filtered) {
            $excel->sheet('Master', function ($sheet) use ($filtered) {
                $sheet->fromArray(
                    $filtered->all()
                );
            });
        })->export('xls');
    }
}
