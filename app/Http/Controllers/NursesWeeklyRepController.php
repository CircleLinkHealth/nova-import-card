<?php

namespace App\Http\Controllers;

use App\Services\NursesWeeklyReportService;
use App\User;
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
        //$yesterdayDate = Carbon::yesterday();
        //if the admin loads the page today, we need to display last night's report
     /*   if ($request->has('date')) {
            $requestDate = new Carbon($request['date']);
           $date        = $requestDate->copy();
        } else {
            return 'sex';
        }*/
            //$date = $yesterdayDate->copy();

         $date = Carbon::parse('2019-01-16');
        //checks date and gets data either from DB or S3
        /*if ($date >= today()) {
         $data = $this->service->showDataFromDb($date);
        } else {
         $data = $this->service->showDataFromS3($date);
         }*/

        $startOfWeek = $date->copy()->subWeek()->startOfWeek();

        $days        = [];

        for ($i = 0; $i <= 6; $i++) {
            $days[] = $startOfWeek->copy()->addDay($i)->toDateString();
        }
        /*foreach ($days as $day) {
            $data[$day] = $this->service->showDataFromDb(Carbon::parse($day));
        }*/
        $yesterdayDate = $date;
        return view('admin.reports.nurseWeekly', compact('days', 'yesterdayDate'));
    }

    public function dayFilter($weekDay)
    {
        $date = Carbon::parse($weekDay)->toDateTimeString();
        // $yesterdayDate = Carbon::today()->subDay(1);

        //if the admin loads the page today, we need to display last night's report
        //  if ($request->has('date')) {
        //      $requestDate = new Carbon($request['date']);
        //      $date        = $requestDate->copy();
        //  } else {
        //      $date = $yesterdayDate->copy();
        //  }
       // $date = Carbon::parse('2019-01-9');
        //checks date and gets data either from DB or S3
        /* if ($date >= today()) {*/
        //$data = $this->service->showDataFromDb($date);
        /* } else {*/
        //$data = $this->service->showDataFromS3($date);
        /*}*/

     /*   $startOfWeek = $date->copy()->startOfDay();
        dd($startOfWeek);
        $days        = [];

        for ($i = 0; $i <= 2; $i++) {
            $days[] = $startOfWeek->copy()->addDay($i)->toDateString();
        }*/
        /*foreach ($date as $day) {*/
            $data[$date] = $this->service->showDataFromDb(Carbon::parse($date));
        /*}*/

        $nurses = User::ofType('care-center')->whereHas('outboundCalls', function ($q) use ($date) {
            $q->where([
                ['scheduled_date', '>=', Carbon::parse($date)->copy()->startOfDay()->toDateString()],
                ['scheduled_date', '<=', Carbon::parse($date)->endOfDay()->toDateTimeString()],
            ])->orWhere([
                ['called_date', '>=', Carbon::parse($date)->copy()->startOfDay()->toDateTimeString()],
                ['called_date', '<=', Carbon::parse($date)->endOfDay()->toDateTimeString()],
            ]);
        })->get();

        $nurseData = [];


            foreach ($nurses as $nurse) {
                $nurseData[$nurse->getFullName()][$date] = $data[$date]->where('nurse_id', $nurse->id);
            }

            dd($nurseData);
        $yesterdayDate = $date;

        return view('admin.reports.nurseweekly', compact('nurseData', 'yesterdayDate', 'date', 'days'));
    }
}
