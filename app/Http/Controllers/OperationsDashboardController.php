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
     * Gets Patient Counts for table: CarePlan Manager Patient Totals,
     * for today, for specific day.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $date     = Carbon::today();
        $dateType = 'day';


        //active practices for dropdown.
        $practices = Practice::active()->get();


        $totals             = $this->service->getCpmPatientTotals($date, 'day');
        $patientsByPractice = null;
        $practice           = false;


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
     * Gets Patient Counts for table: CarePlan Manager Patient Totals
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getTotalPatientData(Request $request)
    {
        $practice           = false;
        $patientsByPractice = null;

        $date = Carbon::createFromFormat('Y-m-d', $request['date']);
        $dateType = $request['type'];

        $practices = Practice::active()->get();

        $totals = $this->service->getCpmPatientTotals($date, $dateType);

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
     * Gets Patient Counts for table: Patient stats by Practice.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPatientsByPractice(Request $request)
    {

        $totals = null;

        $date     = new Carbon($request['date']);
        $dateType = $request['type'];
        $practice = Practice::find($request['practice_id']);


        $practices = Practice::active()->get();
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
     * Gets Patient list for Total Patients Table, and Patients by Practice Table.
     *
     *
     * @param Request $request
     *
     * @param $type (type of the column (day, week, month, total)
     * @param $date
     * @param $dateType (type of date for the query (specific day, week, or month)
     * @param null $practiceId
     *
     * @return mixed
     */
    public function getList(Request $request, $type, $date, $dateType, $practiceId = null)
    {

        $to     = null;
        $date   = new Carbon($date);
        $toDate = null;



        if ($type == 'day') {
            if ($dateType == 'day') {
                $dayDate  = $date->copy()->toDateString();
                $patients = $this->service->getTotalPatients($dayDate);
            }
            if ($dateType == 'week') {
                $dayDate  = $date->copy()->endOfWeek()->toDateString();
                $patients = $this->service->getTotalPatients($dayDate);
            }
            if ($dateType == 'month') {
                $dayDate  = $date->copy()->endOfMonth()->toDateString();
                $patients = $this->service->getTotalPatients($dayDate);
            }
        }
        if ($type == 'week') {
            if ($dateType == 'day' || 'week') {
                $fromDate = $date->copy()->startOfWeek()->toDateString();
                $toDate   = $date->copy()->endOfWeek()->toDateString();
                $patients = $this->service->getTotalPatients($fromDate, $toDate);
            }
            if ($dateType == 'month') {
                $fromDate = $date->copy()->endOfMonth()->startOfWeek()->toDateString();
                $toDate   = $date->copy()->endOfMonth()->toDateString();
                $patients = $this->service->getTotalPatients($fromDate, $toDate);
            }
        }
        if ($type == 'month') {
            $fromDate = $date->copy()->startOfMonth()->toDateString();
            $toDate   = $date->copy()->endOfMonth()->toDateString();
            $patients = $this->service->getTotalPatients($fromDate, $toDate);
        }
        if ($type == 'total') {
            $patients = $this->service->getTotalPatients();
        }

        $practice = null;
        if ($practiceId) {
            $practice = Practice::find($practiceId);
            $patients = $this->service->filterPatientsByPractice($patients, $practiceId);
        }


        return view('admin.opsDashboard.list', compact([
            'patients',
            'type',
            'date',
            'practice',
            'to',
        ]));

    }


    /**
     * Gets Paused Patients List for two specific dates.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPausedPatientList(Request $request)
    {

        $practice = null;
        $date     = new Carbon($request['fromDate']);
        $to       = new Carbon($request['toDate']);

        $fromDate = $date->toDateString();
        $toDate   = $to->toDateString();

        $patients = $this->service->getPausedPatients($fromDate, $toDate);

        return view('admin.opsDashboard.list', compact([
            'patients',
            'practice',
            'date',
            'to',
        ]));
    }

    public function getPatientNotesAndActivitiesPage(Request $request)
    {

    }


}
