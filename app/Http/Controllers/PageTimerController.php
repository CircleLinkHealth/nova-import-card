<?php namespace App\Http\Controllers;

use App\Activity;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\PageTimer;
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
        $this->activityService = $activityService;
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

        $patientId  = $request->input('patientId');
        $providerId = $data['providerId'] ?? null;

        $totalTime = $data['totalTime'] ?? 0;

        $duration = ceil($totalTime / 1000);

        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $data['startTime']);
        $endTime   = $startTime->copy()->addSeconds($duration);

        $loc = $data['redirectLocation'] ?? null;

        $redirectTo = empty($loc)
            ? null
            : $loc;

        $newActivity                    = new PageTimer();
        $newActivity->redirect_to       = $redirectTo;
        $newActivity->billable_duration = 0;
        $newActivity->duration          = $duration;
        $newActivity->duration_unit     = 'seconds';
        $newActivity->patient_id        = $patientId;
        $newActivity->provider_id       = $providerId;
        $newActivity->start_time        = $startTime->toDateTimeString();
        $newActivity->actual_start_time = $startTime->toDateTimeString();
        $newActivity->actual_end_time   = $endTime->toDateTimeString();
        $newActivity->end_time          = $endTime->toDateTimeString();
        $newActivity->url_full          = $data['urlFull'];
        $newActivity->url_short         = $data['urlShort'];
        $newActivity->program_id        = $data['programId'];
        $newActivity->ip_addr           = $data['ipAddr'];
        $newActivity->activity_type     = $data['activity'];
        $newActivity->title             = $data['title'];
        $newActivity->user_agent        = $request->userAgent();
        $newActivity->billable_duration = $duration;
        $newActivity->end_time          = $startTime->addSeconds($duration)->toDateTimeString();
        $newActivity->save();

        $activityId = $this->addPageTimerActivities($newActivity);

        if ($activityId) {
            $this->handleNurseLogs($activityId);
        }

        return response("PageTimer Logged, duration:" . $duration, 201);
    }

    public function addPageTimerActivities(PageTimer $pageTimer)
    {
        // check params to see if rule exists
        $params = [];

        //user
        $user = User::find($pageTimer->provider_id);

        if ( ! (bool)$user->isCCMCountable() || $pageTimer->patient_id == 0) {
            return false;
        }

        // activity param
        $params['activity'] = $pageTimer->activity_type;

        $omitted_routes = [
            'patient.activity.create',
            'patient.activity.providerUIIndex',
        ];

        $is_ommited = in_array($pageTimer->title, $omitted_routes);

        if ( ! $is_ommited) {
            $activityParams                  = [];
            $activityParams['type']          = $params['activity'];
            $activityParams['provider_id']   = $pageTimer->provider_id;
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

    public function handleNurseLogs($activityId)
    {

        $activity = Activity::find($activityId);

        if ($activity) {
            $nurse    = User::find($activity->provider_id)->nurseInfo;
            if ($nurse) {
                $alternativePayComputer = new AlternativeCareTimePayableCalculator($nurse);
    
                $alternativePayComputer->adjustCCMPaybleForActivity($activity);
            }
        }
        
        return false;
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
}
