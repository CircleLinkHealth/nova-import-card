<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\SaasAccount;
use App\Services\NursesWeeklyReportService;
use App\User;
use Carbon\Carbon;

class NursesWeeklyRepController extends Controller
{
    private $service;

    public function __construct(NursesWeeklyReportService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date   = Carbon::parse('2019-1-07 00:00:00');//Carbon::now()->startOfWeek()->startOfDay();

        if ($date >= today()){
            $data = $this->service->getDataFromDb($date);
        }else {
            $data = $this->service->getDataFromS3($date);
        }
        return view('admin.reports.nurseweekly', compact('data'));
    }
}
