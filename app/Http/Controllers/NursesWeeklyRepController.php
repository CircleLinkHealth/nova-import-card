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

        $data = $this->service->munipulateData($days);

        return view('admin.reports.nurseWeekly', compact(
            'days',
            'date',
            'yesterdayDate',
            'data',
            'startOfWeek',
            'upToDayOfWeekForUi'
        ));
    }
}
