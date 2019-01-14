<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NursesWeeklyReportService;
use Carbon\Carbon;

class NursesWeeklyRepController extends Controller
{
    private $service;

    public function __construct(NursesWeeklyReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $dataIfNoDateSelected = Carbon::today()->subDay(1);
        //if the admin loads the page today, we need to display last night's report
        if ($request->has('date')) {
            $requestDate = new Carbon($request['date']);
            $date        = $requestDate->copy();
        } else {
            $date = $dataIfNoDateSelected->copy();
        }
        //$date = Carbon::parse('2019-1-07 00:00:00');//Carbon::now()->startOfWeek()->startOfDay();

        if ($date >= today()) {
            $data = $this->service->showDataFromDb($date);
        } else {
            $data = $this->service->showDataFromS3($date);
        }

        return view('admin.reports.nurseweekly', compact('data', 'dataIfNoDateSelected', 'date'));
    }

}
