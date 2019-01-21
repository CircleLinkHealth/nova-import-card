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
//todo:need to add validation to front end Calendar input
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
        //get all nurses for all days - will need names to add default values **
        $nursesNames = [];
        foreach ($dataPerDay as $day => $dataForDay) {
            foreach ($dataForDay as $nurse) {
                $nursesNames[] = $nurse['nurse_full_name'];
            }
        }

        $data = [];
        foreach ($dataPerDay as $day => $dataForDay) {
            if (empty($dataForDay)) {
                // If no data for that day - then go through all nurses and add some default values **
                foreach ($nursesNames as $nurseName) {
                    $data[$nurseName][$day]
                        = [
                        'nurse_full_name' => $nurseName,
                        'committedHours'  => 0,
                        'actualHours'     => 0,
                        'unsuccessful'    => 0,
                        'successful'      => 0,
                        'actualCalls'     => 0,
                        'scheduledCalls'  => 0,
                        'efficiency'      => 0,
                    ];
                }
            }

            //data has per day per nurse
            //need to go into per nurse per day
            foreach ($dataForDay as $nurse) {
                if ( ! isset($data[$nurse['nurse_full_name']])) {
                    $data[$nurse['nurse_full_name']] = [];
                }
                $data[$nurse['nurse_full_name']][$day] = $nurse;
            }
        }

        foreach ($data as $nurseName => $reportPerDayArr) {
            foreach ($days as $day) {
                //if no data array exists for date
                if ( ! isset($reportPerDayArr[$day->toDateString()])) {
                    $data[$nurseName][$day->toDateString()] = [
                        'nurse_full_name' => $nurseName,
                        'committedHours'  => 0,
                        'actualHours'     => 0,
                        'unsuccessful'    => 0,
                        'successful'      => 0,
                        'actualCalls'     => 0,
                        'scheduledCalls'  => 0,
                        'efficiency'      => 0,
                    ];
                }
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
