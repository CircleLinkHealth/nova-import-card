<?php

namespace App\Http\Controllers;

use App\Practice;
use App\Services\OperationsDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        return view('admin.opsDashboard.index', compact([
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
    public function getTotalPatientData(Request $request){

        if ($request['dayDate']){
            $dateType = 'day';
            $date = new Carbon($request['dayDate']);
        }
        if ($request['weekDate']){
            $dateType = 'week';
            $date = new Carbon($request['weekDate']);
        }
        if ($request['monthDate']){
            $dateType = 'month';
            $date = new Carbon($request['monthDate']);
        }

        $practices = Practice::active()->get();

        $totals = $this->service->getCpmPatientTotals($date, $dateType);

        $patientsByPractice = null;

        return view('admin.opsDashboard.index', compact([
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

        if (!$request['totalDate']){
            return $this->badRequest('Invalid [totalDate] parameter. Must have a value."');
        }
        $date = new Carbon($request['totalDate']);

        if (!$request['listType']){
            return $this->badRequest('Invalid [listType] parameter."');
        }


        if ($request['listType'] == 'day'){
            $dayDate = $date->toDateString();
            $patients = $this->service->getTotalPatients($dayDate);
        }
        if ($request['listType'] == 'week'){
            $fromDate = $date->startOfWeek()->toDateString();
            $toDate = $date->endOfWeek()->toDateString();
            $patients = $this->service->getTotalPatients($fromDate, $toDate);
        }
        if ($request['listType'] == 'month'){
            $fromDate = $date->startOfMonth()->toDateString();
            $toDate = $date->endOfMonth()->toDateString();
            $patients = $this->service->getTotalPatients($fromDate, $toDate);
        }
        if ($request['listType'] == 'total'){

            $patients = $this->service->getTotalPatients();
        }

        //return a view that contains detailed list?
        return $patients;

    }

    public function getPausedPatientList(Request $request){

        $from = new Carbon($request['fromDate']);
        $to = new Carbon($request['toDate']);

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $patients = $this->service->getPausedPatients($fromDate, $toDate);

        return view('admin.opsDashboard.list', compact([
            'patients'
        ]));
    }

    public function getPatientNotesAndActivitiesPage(Request $request){

    }




}
