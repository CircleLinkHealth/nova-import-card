<?php


namespace Circlelinkhealth\GenerateSuccessStoriesReport;

use App\Exports\SuccessStoriesExport;
use Carbon\Carbon;

class GenerateSuccessStoriesReportController
{
    public function handle($monthYear)
    {
        $month = Carbon::parse($monthYear);
        $fileName = 'success-stories' . $monthYear . '.csv';
        return \Excel::download(new SuccessStoriesExport($month), $fileName);
    }
}