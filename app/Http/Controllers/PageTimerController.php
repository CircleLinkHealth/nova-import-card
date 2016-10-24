<?php namespace App\Http\Controllers;

use App\Activity;
use App\PageTimer;
use App\Services\ActivityService;
use App\Services\RulesService;
use App\Services\TimeTracking\Service as TimeTrackingService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PageTimerController extends Controller
{
    protected $timeTrackingService;

    public function __construct(
        Request $request,
        TimeTrackingService $timeTrackingService
    ) {
        parent::__construct($request);

        $this->timeTrackingService = $timeTrackingService;
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
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->input();

        $providerId = $data['providerId'];

        //We have the duration from two sources.
        //On page JS timer
        //Difference between start and end dates on the server
        $duration = ceil($data['totalTime'] / 1000);

        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $data['startTime']);
//        $endTimeNow = Carbon::now();
        $endTime = $startTime->copy()->addSeconds($duration);

//        if (!in_array($data['redirectLocation'], [
//            'logout',
//            'home',
//        ])
//        ) {
//            $endTimeNowStartTimeDifference = $startTime->diffInSeconds($endTimeNow);
//
//            if ($endTimeNowStartTimeDifference > $duration) {
//                $endTime = $endTimeNow;
//                $duration = $endTimeNowStartTimeDifference;
//            }
//        }

        if (app()->environment('testing') || isset($data['testing'])) {
            $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $data['testEndTime']);
            $duration = $startTime->diffInSeconds($endTime);
        }

        $loc = $data['redirectLocation'] ?? null;

        $redirectTo = empty($loc)
            ? null
            : $loc;

        $newActivity = new PageTimer();
        $newActivity->redirect_to = $redirectTo;
        $newActivity->billable_duration = 0;
        $newActivity->duration = $duration;
        $newActivity->duration_unit = 'seconds';
        $newActivity->patient_id = $data['patientId'];
        $newActivity->provider_id = $providerId;
        $newActivity->start_time = $startTime->toDateTimeString();
        $newActivity->actual_start_time = $startTime->toDateTimeString();
        $newActivity->actual_end_time = $endTime->toDateTimeString();
        $newActivity->end_time = $endTime->toDateTimeString();
        $newActivity->url_full = $data['urlFull'];
        $newActivity->url_short = $data['urlShort'];
        $newActivity->program_id = $data['programId'];
        $newActivity->ip_addr = $data['ipAddr'];
        $newActivity->activity_type = $data['activity'];
        $newActivity->title = $data['title'];

        $overlaps = PageTimer::where('provider_id', '=', $providerId)
            ->where([
                [
                    'end_time',
                    '>=',
                    $startTime,
                ],
                [
                    'start_time',
                    '<=',
                    $endTime,
                ],
            ])
            ->where('start_time', '!=', '0000-00-00 00:00:00')
            ->where('end_time', '!=', '0000-00-00 00:00:00')
            ->get();

        if (!$overlaps->isEmpty() && $startTime->diffInSeconds($endTime) > 0) {
            $overlapsAsc = $overlaps->sortBy('start_time');

            $this->timeTrackingService->figureOutOverlaps($newActivity, $overlapsAsc);
        } else {
            $newActivity->billable_duration = $duration;
            $newActivity->end_time = $startTime->addSeconds($duration)->toDateTimeString();
            $newActivity->save();
        }

        $this->addPageTimerActivities($newActivity);

        return response("PageTimer Logged, duration:" . $duration, 201);
    }

    /**
     * Add an activity for a page time
     *
     * @param array $page_timer_ids
     *
     * @return bool
     */
    public function addPageTimerActivities(PageTimer $pageTimer)
    {
        // check params to see if rule exists
        $params = [];

        //provider
        $provider = User::find($pageTimer->provider_id);

        // provider role param
        $params['role'] = '';
        $role = $provider->roles()->first();
        if ($role) {
            $params['role'] = $role->name;
        }

        // activity param
        $params['activity'] = $pageTimer->activity_type;

        // check against rules and add activity if passes
        $rulesService = new RulesService;
        $ruleActions = $rulesService->getActions($params, 'ATT');

        if ($ruleActions) {
            $activiyParams = [];
            $activiyParams['type'] = $params['activity'];
            $activiyParams['provider_id'] = $pageTimer->provider_id;
            $activiyParams['performed_at'] = $pageTimer->start_time;
            $activiyParams['duration'] = $pageTimer->billable_duration;
            $activiyParams['duration_unit'] = 'seconds';
            $activiyParams['patient_id'] = $pageTimer->patient_id;
            $activiyParams['logged_from'] = 'pagetimer';
            $activiyParams['logger_id'] = $pageTimer->provider_id;
            $activiyParams['page_timer_id'] = $pageTimer->id;

            // if rule exists, create activity
            $activityId = Activity::createNewActivity($activiyParams);

            $activityService = new ActivityService;
            $result = $activityService->reprocessMonthlyActivityTime($pageTimer->patient_id);
        }

        // update pagetimer
        $pageTimer->processed = 'Y';
        $pageTimer->rule_params = serialize($params);
        $pageTimer->rule_id = ($ruleActions)
            ? $ruleActions[0]->id
            : '';
        $pageTimer->save();

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $pageTime = PageTimer::find($id);

        return view('pageTimer.show', ['pageTime' => $pageTime]);
    }

    public function closePatientSession(Request $request)
    {
        //This is intentionally left blank!
        //All the logic happens in Controller, because of some restrictions with Laravel at the time I'm writing this,
        //that's the best way I can come up with right now. Gross, I know, but it's 3:30am on a Saturday
    }

}
