<?php

namespace App\Http\Controllers\Admin\Reports;

use App\CLH\CCD\ItemLogger\CcdDemographicsLog;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Maatwebsite\Excel\Facades\Excel;

class EthnicityReportController extends Controller
{
    public function getReport()
    {
        $data = CcdDemographicsLog::get([
            'first_name',
            'last_name',
            'dob',
            'race',
            'ethnicity'
        ]);

        Excel::create("Ethnicity Report", function ($excel) use ($data) {
            $excel->sheet('Master', function ($sheet) use ($data) {
                $sheet->fromArray(
                    $data
                );
            });
        })->export('xls');
    }
}
