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
        $fromDate = $date->startOfMonth()->toDateTimeString();
        $toDate = $date->endOfMonth()->toDateTimeString();

        //active practices for dropdown.
        $practices = Practice::active()->get();


        $totals = $this->service->getCpmPatientTotals($date, 'day');
        $patientsByPractice = null;

        return view('opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice'
        ]));

    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPatientData(Request $request){

        $date = new Carbon($request['totalDate']);
        $fromDate = $date->startOfMonth()->toDateTimeString();
        $toDate = $date->endOfMonth()->toDateTimeString();


        $practices = Practice::active()->get();

        $totals = $this->service->getCpmPatientTotals($date, $request['totalDateType']);

        $patientsByPractice = null;
        if ($request['practiceId']){
            $patientsByPractice = $this->service->filterPatientsByPractice($this->service->getTotalPatients($fromDate, $toDate), $request['practiceId']);
        }


        return view('opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice'
        ]));

    }


    /**
     * gets Patient list for selected column from Patient Totals table.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getList(Request $request){

//        $date = new Carbon($request['date']);
//
//        $fromDate = $date->startOfMonth()->toDateTimeString();
//        $toDate   = $date->endOfMonth()->toDateTimeString();
//
//        $total = $this->service->getTotalPatients($fromDate, $toDate);
//

//        if ($request['listType'] == 'day'){
//            $patients = $this->service->filterPatients($total, $date);
//        }
//        if ($request['listType'] == 'week'){
//            $fromDate = $request['date']->startOfWeek();
//            $toDate = $request['date']->endOfWeek();
//            $patients = $this->service->filterPatients($total, $fromDate, $toDate);
//        }
//        if ($request['listType'] == 'month'){
//            $fromDate = $request['date']->startOfMonth();
//            $toDate = $request['date']->endOfMonth();
//            $patients = $this->service->filterPatients($total, $fromDate, $toDate);
//        }

//        return $patients;

    }

    public function getPausedPatientList(Request $request){

    }

    public function getPatientNotesAndActivitiesPage(Request $request){

    }




}
