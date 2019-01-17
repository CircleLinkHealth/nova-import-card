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

    public function calendarInput()
    {
        return view('admin.reports.nursesWeeklyReportForm');
    }

    public function index(Request $request)
    {
        $date = Carbon::parse($request->input('date'));
        $startOfWeek = $date->copy()->subWeek()->startOfWeek();
        $days = [];
        for ($i = 0; $i <= 2; $i++) {
            $days[] = $startOfWeek->copy()->addDay($i)->toDateString();
        }
        if ($date >= today()->startOfDay()) {
            //$data = $this->service->showDataFromDb($date);
            return 'Please input past date';
        } else {
            foreach ($days as $day) {
                $data[$day] = $this->service->showDataFromS3(Carbon::parse($day));
            }
            //$data = $this->service->showDataFromS3($date);
        }

        $nurses = User::ofType('care-center')->whereHas('outboundCalls', function ($q) use ($day) {
            $q->where([
                ['scheduled_date', '>=', Carbon::parse($day)->copy()->startOfDay()->toDateString()],
                ['scheduled_date', '<=', Carbon::parse($day)->endOfDay()->toDateTimeString()],
            ])->orWhere([
                ['called_date', '>=', Carbon::parse($day)->copy()->startOfDay()->toDateTimeString()],
                ['called_date', '<=', Carbon::parse($day)->endOfDay()->toDateTimeString()],
            ]);
        })->get();

        $nurseData = [];
        foreach ($nurses as $nurse) {
            $nurseData[$nurse->getFullName()][$day] = $data[$day]->where('nurse_id', $nurse->id);
        }
        $yesterdayDate = $date;

        return view('admin.reports.nurseWeekly', compact('days', 'yesterdayDate', 'date', 'nurseData', 'data'));
    }

    public function dayFilter($weekDay)
    {
        $date        = $weekDay;
        $startOfWeek = Carbon::parse($date)->copy()->subWeek()->startOfWeek();
        $days        = [];

        for ($i = 0; $i <= 2; $i++) {
            $days[] = $startOfWeek->copy()->addDay($i)->toDateString();
        }

        $data[$date] = $this->service->showDataFromDb(Carbon::parse($date));

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

        return view('admin.reports.nurseweekly', compact('days', 'yesterdayDate', 'date', 'nurseData', 'data'));
    }
}
