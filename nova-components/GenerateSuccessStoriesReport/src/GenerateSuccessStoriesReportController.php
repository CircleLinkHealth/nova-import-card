<?php


namespace Circlelinkhealth\GenerateSuccessStoriesReport;

use App\Exports\SuccessStoriesExport;
use Carbon\Carbon;

class GenerateSuccessStoriesReportController
{
    public function handle($monthYear)
    {
        $month = Carbon::parse($monthYear);
        return \Excel::download(new SuccessStoriesExport($month), 'success.csv');
    }
}