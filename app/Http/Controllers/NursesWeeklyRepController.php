<?php

namespace App\Http\Controllers;

use App\Services\NursesAndStatesDailyReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NursesWeeklyRepController extends Controller
{
    const NO_DATA_ON_S3_BEFORE = '2019-02-03';
    private $service;

    public function __construct(NursesAndStatesDailyReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $yesterdayDate = Carbon::yesterday()->startOfDay();
        $limitDate     = Carbon::parse(NursesWeeklyRepController::NO_DATA_ON_S3_BEFORE);

        if ($request->has('date')) {
            $requestDate = new Carbon($request['date']);
            $date        = $requestDate->copy();
        } else {
            $date = $yesterdayDate->copy();
        }

        if ($date->gte(today()->startOfDay())) {
            $messages['errors'][] = 'Please input a past date';

            return redirect()->back()->withErrors($messages);
        }

        $days          = [];
        $startOfWeek   = $date->copy()->startOfWeek();
        $upToDayOfWeek = carbonToClhDayOfWeek($date->dayOfWeek);

        for ($i = 0; $i < $upToDayOfWeek; $i++) {
            $days[] = $startOfWeek->copy()->addDay($i);
        }
        //data are returned in 2 arrays. {Data} and the {Totals of data}.
        $nurses = $this->service->manipulateData($days, $limitDate);
        $totals = $nurses->only('totals');
        $data   = $nurses->forget('totals');

        return view('admin.reports.nurseWeekly', compact(
            'days',
            'date',
            'totals',
            'yesterdayDate',
            'data',
            'startOfWeek',
            'limitDate'
        ));
    }
}
