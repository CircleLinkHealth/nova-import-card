<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Activity;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
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
            'offline-activity-time-requests.create',
            'patient.activity.create',
            'patient.activity.providerUIIndex',
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
        $data = $request->input();

        $patientId  = $request->input('patientId');
        $providerId = $data['providerId'] ?? null;

        foreach ($data['activities'] as $activity) {
            $duration = $activity['duration'];

            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $activity['start_time']);
            $endTime   = $startTime->copy()->addSeconds($duration);

            $redirectTo = $data['redirectLocation'] ?? null;

            $isBhi = User::isBhiChargeable()
                         ->where('id', $patientId)
                         ->exists();

            $newActivity                    = new PageTimer();
            $newActivity->redirect_to       = $redirectTo;
            $newActivity->billable_duration = $duration;
            $newActivity->duration          = $duration;
            $newActivity->duration_unit     = 'seconds';
            $newActivity->patient_id        = $patientId;
            $newActivity->provider_id       = $providerId;
            $newActivity->start_time        = $startTime->toDateTimeString();
            $newActivity->end_time          = $endTime->toDateTimeString();
            $is_behavioral                  = isset($activity['is_behavioral'])
                ? (bool)$activity['is_behavioral'] && $isBhi
                : $isBhi;
            $newActivity->url_full          = $activity['url'];
            $newActivity->url_short         = $activity['url_short'];
            $newActivity->program_id        = $data['programId'];
            $newActivity->ip_addr           = $data['ipAddr'];
            $newActivity->activity_type     = $activity['name'];
            $newActivity->title             = $activity['title'];
            $newActivity->user_agent        = $request->userAgent();
            $newActivity->save();

            $activityId = null;

            if ($newActivity->billable_duration > 0) {
                $activityId = $this->addPageTimerActivities($newActivity, $is_behavioral);
            }

            if ($activityId) {
                $this->handleNurseLogs($activityId);
            }
        }

        return response('PageTimer activities logged.', 201);
    }
}
