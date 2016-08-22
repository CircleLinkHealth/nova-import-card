<?php

namespace App\Http\Controllers\Admin\Reports;

use App\CLH\CCD\ItemLogger\CcdDemographicsLog;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\CCD\CcdVendor;
use App\Program;
use Maatwebsite\Excel\Facades\Excel;

class EthnicityReportController extends Controller
{
    public function getReport()
    {
        $data = CcdDemographicsLog::all();

        //Prepare spreadsheet data
        $filtered = $data->map(function ($demoLog){
            $ccdVendor = CcdVendor::find($demoLog->vendor_id);
            $program = Program::find($ccdVendor->program_id);

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
