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
//todo:need to add validation to front end also
        if ($date >= today()->startOfDay()) {
            $messages['errors'][] = 'Please input a date in the past.';
            return redirect()->back()->withErrors($messages);
        }

        $dataPerDay = [];
        foreach ($days as $day) {
            try {
                $dataPerDay[$day->toDateString()] = $this->service->showDataFromS3($day);
            } catch (\Exception $e) {
                $dataPerDay[$day->toDateString()] = []; //todo: return something here
            }
        }
        //data has per day per nurse
        //need to go into per nurse per day
        $data       = [];
        $efficiency = [];
        foreach ($dataPerDay as $day => $dataForDay) {
            foreach ($dataForDay as $nurse) {
                //todo:values are converted to hrs - is /10 correct? in ops dashboard vals are in sec and divided/100
                // $efficiency = round((float)($nurse['activityTime'] / $nurse['actualHours']) * 10);
                if ( ! isset($data[$nurse['nurse_full_name']])) {
                    $data[$nurse['nurse_full_name']] = [];
                }
                $data[$nurse['nurse_full_name']][$day] = $nurse;
            }
        }

        return view('admin.reports.nurseWeekly', compact([
            'days',
            'date',
            'yesterdayDate',
            'data',
            'startOfWeek',
            'upToDayOfWeekForUi',
        ]));
    }

}
