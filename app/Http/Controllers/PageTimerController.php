<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Activity;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Jobs\StoreTimeTracking;
use App\Nurse;
use App\PageTimer;
use App\PatientMonthlySummary;
use App\Services\ActivityService;
use App\Services\TimeTracking\Service as TimeTrackingService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PageTimerController extends Controller
{
    protected $activityService;
    protected $timeTrackingService;

    public function __construct(
        Request $request,
        TimeTrackingService $timeTrackingService,
        ActivityService $activityService
    ) {
        $this->activityService     = $activityService;
        $this->timeTrackingService = $timeTrackingService;
    }

    public function addPageTimerActivities(PageTimer $pageTimer, $is_behavioral = false)
    {
        // check params to see if rule exists
        $params = [];

        //user
        $user = User::find($pageTimer->provider_id);

        if (( ! (bool)$user->isCCMCountable()) || (0 == $pageTimer->patient_id)) {
            return false;
        }

        // activity param
        $params['activity'] = $pageTimer->activity_type;

        $omitted_routes = [
            'patient.activity.create',
            'patient.activity.providerUIIndex',
            'patient.reports.progress',
        ];

        $is_ommited = in_array($pageTimer->title, $omitted_routes);

        if ( ! $is_ommited) {
            $activityParams                  = [];
            $activityParams['type']          = $params['activity'];
            $activityParams['provider_id']   = $pageTimer->provider_id;
            $activityParams['is_behavioral'] = $is_behavioral;
            $activityParams['performed_at']  = $pageTimer->start_time;
            $activityParams['duration']      = $pageTimer->billable_duration;
            $activityParams['duration_unit'] = 'seconds';
            $activityParams['patient_id']    = $pageTimer->patient_id;
            $activityParams['logged_from']   = 'pagetimer';
            $activityParams['logger_id']     = $pageTimer->provider_id;
            $activityParams['page_timer_id'] = $pageTimer->id;

            // if rule exists, create activity
            $activityId = Activity::createNewActivity($activityParams);

            $this->activityService->processMonthlyActivityTime([$pageTimer->patient_id]);

            $pageTimer->processed = 'Y';

            $pageTimer->save();

            return $activityId;
        }

        // update pagetimer
        $pageTimer->processed = 'Y';

        $pageTimer->save();

        return false;
    }

    public function getTimeForPatients(Request $request)
    {

        $patients = $request->get('patients', []);

        if (empty($patients)) {
            return response()->json([]);
        }

        $times = PatientMonthlySummary::whereIn('patient_id', $patients)
                                      ->whereMonthYear(Carbon::now()->startOfMonth())
                                      ->orderBy('id', 'desc')
                                      ->get([
                                          'ccm_time',
                                          'patient_id',
                                      ])
                                      ->mapWithKeys(function ($p) {
                                          return [
                                              $p->patient_id => [
                                                  'ccm_time' => $p->ccm_time ?? 0,
                                                  'bhi_time' => $p->bhi_time ?? 0,
                                              ],
                                          ];
                                      })
                                      ->all();

        return response()->json($times);
    }

    public function handleNurseLogs($activityId)
    {
        $activity = Activity::with('patient.patientInfo')
                            ->find($activityId);

        if ( ! $activity) {
            return;
        }

        $nurse = Nurse::whereUserId($activity->provider_id)
                      ->first();

        if ( ! $nurse) {
            return;
        }

        $alternativePayComputer = new AlternativeCareTimePayableCalculator($nurse);
        $alternativePayComputer->adjustNursePayForActivity($activity);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $pageTimes = PageTimer::orderBy('id', 'desc')->paginate(10);

        return view('pageTimer.index', ['pageTimes' => $pageTimes]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $pageTime = PageTimer::find($id);

        return view('pageTimer.show', ['pageTime' => $pageTime]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        StoreTimeTracking::dispatch($request)->onQueue('high');

        return response('PageTimer activities logged.', 201);
    }
}
