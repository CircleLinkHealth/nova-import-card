<?php namespace App\Http\Controllers;

use App\Activity;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Models\PatientSession;
use App\PageTimer;
use App\Services\ActivityService;
use App\Services\TimeTracking\Service as TimeTrackingService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maknz\Slack\Facades\Slack;

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

        $patientId = $request->input('patientId');
        $providerId = $data['providerId'] ?? null;

        $totalTime = $data['totalTime'] ?? 0;

        //We have the duration from two sources.
        //On page JS timer
        //Difference between start and end dates on the server
        $duration = ceil($totalTime / 1000);

        if ($totalTime < 1) {
            $error = __METHOD__ . ' ' . __LINE__;
            $message = "Time Tracking Error: $error" . PHP_EOL;
            $message .= " Data: " . json_encode($data);
            $message .= " Env: " . env('APP_ENV');

            sendSlackMessage('#time-tracking-issues', $message);
        }

        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $data['startTime']);
        $endTime = $startTime->copy()->addSeconds($duration);

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
        $newActivity->patient_id = $patientId;
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

        if ($patientId) {
            $exists = PatientSession::where('user_id', '=', $providerId)
                ->where('patient_id', '=', $patientId)
                ->exists();


            //If the user does not have an open session with this patient, save page timer as soft deleted and go back.
            if (!$exists) {
//                $newActivity->processed = 'N';
                $newActivity->redirect_to = 'Double Tab';
//                $newActivity->save();
//                $newActivity->delete();
//
//                return response('', 200);
            }
        }


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

        //

        if ($newActivity->billable_duration > 3600) {
            $error = __METHOD__ . ' ' . __LINE__;
            $message = "Time Tracking Error: $error" . PHP_EOL . PHP_EOL;
            $message .= " Data From Browser: " . json_encode($data) . PHP_EOL . PHP_EOL;
            $message .= " PageTimer Object id {$newActivity->id}: " . json_encode($newActivity) . PHP_EOL . PHP_EOL;
            $message .= " Env: " . env('APP_ENV');

            sendSlackMessage('#time-tracking-issues', $message);

            $newActivity->delete();

            return response('', 200);

        }

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

        if (!(bool)$user->isCCMCountable() || $pageTimer->patient_id == 0) {
            return false;
        }

        // user role param
        $params['role'] = '';
        $role = $user->roles()->first();
        if ($role) {
            $params['role'] = $role->name;
        }

        // activity param
        $params['activity'] = $pageTimer->activity_type;

        $omitted_routes = [
            'patient.activity.create',
            'patient.activity.providerUIIndex',
        ];

        $is_ommited = in_array($pageTimer->title, $omitted_routes);

        if (!$is_ommited) {
            $activityParams = [];
            $activityParams['type'] = $params['activity'];
            $activityParams['provider_id'] = $pageTimer->provider_id;
            $activityParams['performed_at'] = $pageTimer->start_time;
            $activityParams['duration'] = $pageTimer->billable_duration;
            $activityParams['duration_unit'] = 'seconds';
            $activityParams['patient_id'] = $pageTimer->patient_id;
            $activityParams['logged_from'] = 'pagetimer';
            $activityParams['logger_id'] = $pageTimer->provider_id;
            $activityParams['page_timer_id'] = $pageTimer->id;

            // if rule exists, create activity
            $activityId = Activity::createNewActivity($activityParams);

            $activityService = new ActivityService;
            $result = $activityService->reprocessMonthlyActivityTime($pageTimer->patient_id);

            $pageTimer->processed = 'Y';
            $pageTimer->rule_params = serialize($params);

            $pageTimer->save();

            return $activityId;
        }

        // update pagetimer
        $pageTimer->processed = 'Y';
        $pageTimer->rule_params = serialize($params);

        $pageTimer->save();

        return false;
    }

    public function handleNurseLogs($activityId)
    {

        $activity = Activity::find($activityId);
        $nurse = User::find($activity->provider_id)->nurseInfo;

        if ($nurse) {

            $alternativePayComputer = new AlternativeCareTimePayableCalculator($nurse);

            $alternativePayComputer->adjustCCMPaybleForActivity($activity);

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

    public function closePatientSession(Request $request)
    {
        //This is intentionally left blank!
        //All the logic happens in Controller, because of some restrictions with Laravel at the time I'm writing this,
        //that's the best way I can come up with right now. Gross, I know, but it's 3:30am on a Saturday
    }

}
