<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Practice;
use App\Services\OperationsDashboardService;
use Carbon\Carbon;

class OperationsDashboardController extends Controller
{

    private $service;

    /**
     * OperationsDashboardController constructor.
     *
     * @param OperationsDashboardService $service
     */
    public function __construct(
        OperationsDashboardService $service
    ) {
        $this->service = $service;

    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $date = Carbon::today();
        $fromDate = $date->startOfMonth();
        $toDate = $date->endOfMonth();

        //active practices for dropdown.
        $practices = Practice::active();

        $pausedPatients = $this->service->getPausedPatients($fromDate, $toDate);
        $totals = $this->service->getCpmPatientTotals($date);




        return view('opsDashboard.index', compact([
            'practices',
            'totals',
            'pausedPatients',
        ]));

    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPatientData(Request $request){


        $practices = Practice::active();

        $totals = $this->service->getCpmPatientTotals($request['totalDate'], $request['totalDateType']);
        $pausedPatients = $this->service->getPausedPatients($request['fromDate'], $request['toDate']);


        $patientsByPractice = null;
        if ($request['practiceId']){
            $patientsByPractice = $this->service->getPatientsByPractice($request['practiceId']);
        }


        return view('opsDashboard.index', compact([
            'practices',
            'totals',
            'pausedPatients',
            'patientsByPractice'
        ]));

    }


}
