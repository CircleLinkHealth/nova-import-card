<?php

namespace App\Http\Controllers;

use App\Services\NursesAndStatesDailyReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NursesWeeklyRepController extends Controller
{
    private $service;

    public function __construct(NursesAndStatesDailyReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $yesterdayDate = Carbon::yesterday()->startOfDay();
        if ($request->has('date')) {
            $requestDate = new Carbon($request['date']);
            $date        = $requestDate->copy();
        } else {
            //if the admin loads the page today, we need to display last day's report
            $date = $yesterdayDate->copy();
        }

        $startOfWeek   = $date->copy()->startOfWeek();
        $days          = [];
        $upToDayOfWeek = carbonToClhDayOfWeek($date->dayOfWeek);

        for ($i = 0; $i < $upToDayOfWeek; $i++) {
            $days[] = $startOfWeek->copy()->addDay($i);
        }

        if ($date >= today()->startOfDay()) {
            $messages['errors'][] = 'Please input a date in the past.';
            return redirect()->back()->withErrors($messages);
        }

        $dataPerDay = [];
        foreach ($days as $day) {
            try {
                $dataPerDay[$day->toDateString()] = $this->service->showDataFromDb($day);
            } catch (\Exception $e) {
                $dataPerDay[$day->toDateString()] = []; //todo: return something here
            }
        }
        //data has per day per nurse
        //need to go into per nurse per day
        $data = [];
        foreach ($dataPerDay as $day => $dataForDay) {
            foreach ($dataForDay as $nurse) {
                if ( ! isset($data[$nurse['nurse_full_name']])) {
                    $data[$nurse['nurse_full_name']] = [];
                }
                $data[$nurse['nurse_full_name']][$day] = $nurse;
            }
        }
        return view('admin.reports.nurseWeekly', compact(['days', 'date', 'data']));
    }

    /*public function dayFilter($weekDay)
    {
        $date        = $weekDay;
        $startOfWeek = Carbon::parse($date)->copy()->subWeek()->startOfWeek();
        $days        = [];

        for ($i = 0; $i <= 2; $i++) {
            $days[] = $startOfWeek->copy()->addDay($i)->toDateString();
        }

        $data[$date] = $this->service->showDataFromS3(Carbon::parse($date));
//dd($data[$date]);
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
        $yesterdayDate = $date;

        // dd($nurseData);

        return view('admin.reports.nurseweekly', compact('days', 'yesterdayDate', 'date', 'nurseData', 'data'));
    }*/
}
