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
        $practice = false;


        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practice',
            'date',
            'dateType',
        ]));

    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getTotalPatientData(Request $request){

        $date = Carbon::createFromFormat('Y-m-d', $request['date']);
//       $date = new Carbon($request['date']);
        $dateType = $request['type'];

        $practices = Practice::active()->get();

//        dd([$date, $request['date']]);

        $totals = $this->service->getCpmPatientTotals($date, $dateType);


        $patientsByPractice = null;
        $practice = false;


        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practice',
            'date',
            'dateType',

        ]));

    }

    public function getPatientsByPractice(Request $request){



        $date = new Carbon($request['date']);
        $dateType = $request['type'];
        $practice = Practice::find($request['practice_id']);


        $practices = Practice::active()->get();


        $totals = null;

        $patientsByPractice = $this->service->getCpmPatientTotals($date, $dateType, $practice->id);


        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practice',
            'date',
            'dateType',
        ]));

    }


    /**
     * gets Patient list for selected column from Patient Totals table.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getList(Request $request, $type, $date, $dateType, $practiceId = null){

        $to = null;
        $date = new Carbon($date);
        $toDate = null;

        //type is the column, dateType is the query type

        if ($type == 'day'){
            if ($dateType == 'day'){
                $dayDate = $date->copy()->toDateString();
                $patients = $this->service->getTotalPatients($dayDate);
            }
            if ($dateType == 'week'){
                $dayDate = $date->copy()->endOfWeek()->toDateString();
                $patients = $this->service->getTotalPatients($dayDate);
            }
            if ($dateType == 'month'){
                $dayDate = $date->copy()->endOfMonth()->toDateString();
                $patients = $this->service->getTotalPatients($dayDate);
            }
        }
        if ($type == 'week'){
            if ($dateType == 'day' || 'week'){
                $fromDate = $date->copy()->startOfWeek()->toDateString();
                $toDate = $date->copy()->endOfWeek()->toDateString();
                $patients = $this->service->getTotalPatients($fromDate, $toDate);
            }
            if ($dateType == 'month'){
                $fromDate = $date->copy()->endOfMonth()->startOfWeek()->toDateString();
                $toDate = $date->copy()->endOfMonth()->toDateString();
                $patients = $this->service->getTotalPatients($fromDate, $toDate);
            }
        }
        if ($type == 'month'){
            $fromDate = $date->copy()->startOfMonth()->toDateString();
            $toDate = $date->copy()->endOfMonth()->toDateString();
            $patients = $this->service->getTotalPatients($fromDate, $toDate);
        }
        if ($type == 'total'){
            $patients = $this->service->getTotalPatients();
        }

        $practice = null;
        if ($practiceId){
            $practice = Practice::find($practiceId);
            $patients = $this->service->filterPatientsByPractice($patients, $practiceId);
        }




        return view('admin.opsDashboard.list', compact([
            'patients',
            'type',
            'date',
            'practice',
            'to'
        ]));

    }

    public function getPausedPatientList(Request $request){

        $practice = null;
        $date = new Carbon($request['fromDate']);
        $to = new Carbon($request['toDate']);

        $fromDate = $date->toDateString();
        $toDate = $to->toDateString();

        $patients = $this->service->getPausedPatients($fromDate, $toDate);

        return view('admin.opsDashboard.list', compact([
            'patients',
            'practice',
            'date',
            'to'
        ]));
    }

    public function getPatientNotesAndActivitiesPage(Request $request){

    }




}
