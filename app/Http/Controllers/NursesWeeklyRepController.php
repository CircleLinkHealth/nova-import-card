<?php

namespace App\Http\Controllers;

use App\Services\NursesWeeklyReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NursesWeeklyRepController extends Controller
{
    private $service;

    public function __construct(NursesWeeklyReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // $yesterdayDate = Carbon::today()->subDay(1);
        //if the admin loads the page today, we need to display last night's report
        //  if ($request->has('date')) {
        //      $requestDate = new Carbon($request['date']);
        //      $date        = $requestDate->copy();
        //  } else {
        //      $date = $yesterdayDate->copy();
        //  }
        $date = Carbon::parse('2019-01-8');
        //checks date and gets data either from DB or S3
        /* if ($date >= today()) {*/
        $data = $this->service->showDataFromDb($date);
        /* } else {*/
        /*     $data = $this->service->showDataFromS3($date);*/

        /*  }*/
        $startOfWeek = $date->copy()->subWeek()->startOfWeek();
        $days = [];
        for ($i = 0; $i <= 7; $i++){
            $days[] = $startOfWeek->copy()->addDay($i)->toDateString();
        }
        foreach($days as $day){
            $data[$day] = $this->service->showDataFromS3(Carbon::parse($day));

        }


        $yesterdayDate = $date;
        return view('admin.reports.nurseweekly', compact('data', 'yesterdayDate', 'date', 'days'));
    }

}
