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

        $dateType = 'day';


        //active practices for dropdown.
        $practices = Practice::active()->get();


        $totals = $this->service->getCpmPatientTotals($date, 'day');
        $patientsByPractice = null;
        $practiceName = 'Not Selected';
        $displayDate = $date->toDateString();


        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practiceName',
            'date',
            'dateType',
            'displayDate'
        ]));

    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getTotalPatientData(Request $request){

        $date = new Carbon($request['date']);
        $dateType = $request['type'];

        $practices = Practice::active()->get();

        $totals = $this->service->getCpmPatientTotals($date, $dateType);

        $patientsByPractice = null;
        $practiceName = 'Not Selected';
        $displayDate = $date->toDateString();

        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practiceName',
            'date',
            'dateType',
            'displayDate'

        ]));

    }

    public function getPatientsByPractice(Request $request){



        $date = new Carbon($request['date']);
        $dateType = $request['type'];
        $displayDate = $date->toDateString();
        $practice = Practice::find($request['practice_id']);
        $practiceName = $practice->display_name;

        $practices = Practice::active()->get();


        $totals = null;

        $patientsByPractice = $this->service->getCpmPatientTotals($date, $dateType, $practice->id);


        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practiceName',
            'date',
            'dateType',
            'displayDate'
        ]));

    }


    /**
     * gets Patient list for selected column from Patient Totals table.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getList(Request $request, $type, $date, $practiceId = null){


        $date = new Carbon($date);



        if ($type == 'day'){
            $dayDate = $date->toDateString();
            $patients = $this->service->getTotalPatients($dayDate);
        }
        if ($type == 'week'){
            $fromDate = $date->startOfWeek()->toDateString();
            $toDate = $date->endOfWeek()->toDateString();
            $patients = $this->service->getTotalPatients($fromDate, $toDate);
        }
        if ($type == 'month'){
            $fromDate = $date->startOfMonth()->toDateString();
            $toDate = $date->endOfMonth()->toDateString();
            $patients = $this->service->getTotalPatients($fromDate, $toDate);
        }
        if ($type == 'total'){
            $patients = $this->service->getTotalPatients();
        }

        //return a view that contains detailed list?
        return view('admin.opsDashboard.list', compact([
            'patients'
        ]));

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
