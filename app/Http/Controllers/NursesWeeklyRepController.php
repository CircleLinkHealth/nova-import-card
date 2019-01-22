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

        if ($date >= today()->startOfDay()) {
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
        $dataMixed    = $this->service->munipulateData($days);
        $data         = $dataMixed['data'];
        $totalsPerDay = $dataMixed['totalsPerDay'];

        return view('admin.reports.nurseWeekly', compact(
            'days',
            'date',
            'totalsPerDay',
            'yesterdayDate',
            'data',
            'startOfWeek'
        ));
    }
}
