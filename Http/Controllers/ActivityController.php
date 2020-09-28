<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Http\Controllers;

use CircleLinkHealth\Customer\NurseTimeAlgorithms\AlternativeCareTimePayableCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShowPatientActivities;
use App\Reports\PatientDailyAuditReport;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\ActivityMeta;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * Class ActivityController.
 */
class ActivityController extends Controller
{
    private $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function create(
        Request $request,
        $patientId
    ) {
        if ( ! $patientId) {
            return abort(404);
        }

        $patient = User::find($patientId);

        if ( ! $patient) {
            return response('User not found', 401);
        }

        $patient_name = $patient->getFullName();

        $userTimeZone = $patient->timezone;

        if (empty($userTimeZone)) {
            $userTimeZone = 'America/New_York';
        }

        $provider_info = User::ofType(['care-center', 'care-center-external', 'provider'])
            ->intersectPracticesWith($patient)
            ->with('roles')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(
                function ($user) use ($patient) {
                    return [
                        $user->id => $user->getFullName().($user->hasRoleForSite(
                            'care-center-external',
                            $patient->primaryProgramId()
                        )
                                ? ' (in-house)'
                                : ''),
                    ];
                }
            )
            ->all();

        $view_data = [
            'program_id'     => $patient->program_id,
            'patient'        => $patient,
            'patient_name'   => $patient_name,
            'activity_types' => Activity::input_activity_types(),
            'provider_info'  => $provider_info,
            'userTimeZone'   => $userTimeZone,
        ];

        return view('wpUsers.patient.activity.create', $view_data);
    }

    public function destroy($id)
    {
    }

    public function downloadAuditReport(ShowPatientActivities $request, $patientId)
    {
        $dateTime = Carbon::createFromDate(
            $request->input('selectYear'),
            $request->input('selectMonth'),
            1
        )->startOfMonth();

        $patient = User::with('patientInfo')->findOrFail($patientId);

        $response = (new PatientDailyAuditReport($patient, $dateTime))->renderPDF();
        $path     = $response['path'];

        return response()->download($path)->deleteFileAfterSend();
    }

    public function getCurrentForPatient($patientId)
    {
        $start = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
        $end   = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
        $acts  = $this->getActivityForPatient($patientId, $start, $end);

        $patient = User::find($patientId);

        return response()->json(
            [
                'monthlyTime'    => $patient->formattedCcmTime(),
                'monthlyBhiTime' => $patient->formattedBhiTime(),
                'table'          => $acts,
            ]
        );
    }

    public function index(Request $request)
    {
        $activities = Activity::orderBy('id', 'desc')->paginate(10);

        return view('activities.index', ['activities' => $activities]);
    }

    public function providerUIIndex(
        ShowPatientActivities $request,
        $patientId
    ) {
        if ($request->has('download-audit-report')) {
            return $this->downloadAuditReport($request, $patientId);
        }
        $patient = User::findOrFail($patientId);

        $input = $request->all();

        $messages = \Session::get('messages');

        if (isset($input['selectMonth'])) {
            $time                = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $start               = $time->startOfMonth()->format('Y-m-d H:i:s');
            $end                 = $time->endOfMonth()->format('Y-m-d H:i:s');
            $month_selected      = $time->format('m');
            $month_selected_text = $time->format('F');
            $year_selected       = $time->format('Y');
        } else {
            $time                = Carbon::now();
            $start               = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
            $end                 = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
            $month_selected      = $time->format('m');
            $month_selected_text = $time->format('F');
            $year_selected       = $time->format('Y');
        }

        $acts = $this->getActivityForPatient($patientId, $start, $end);

        if ($acts) {
            $data = true;
        } else {
            $data = false;
        }

        $reportData = 'data:'.json_encode($acts).'';

        $years = [];
        for ($i = 0; $i < 3; ++$i) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
        ];

        return view(
            'wpUsers.patient.activity.index',
            [
                'activity_json'           => $reportData,
                'years'                   => array_reverse($years),
                'month_selected'          => $month_selected,
                'month_selected_text'     => $month_selected_text,
                'year_selected'           => $year_selected,
                'months'                  => $months,
                'patient'                 => $patient,
                'data'                    => $data,
                'messages'                => $messages,
                'noLiveCountTimeTracking' => true,
            ]
        );
    }

    public function show(
        $patientId,
        $actId
    ) {
        $patient = User::findOrFail($patientId);
        $act     = Activity::findOrFail($actId);

        if ($act->patient_id !== $patient->id) {
            abort(400, 'Not found');
        }

        //Set up note pack for view
        $activity                  = [];
        $messages                  = \Session::get('messages');
        $activity['type']          = $act->type;
        $activity['performed_at']  = $act->performed_at;
        $activity['provider_name'] = User::find($act->provider_id)
            ? (User::find($act->provider_id)->getFullName())
            : '';
        $activity['duration'] = intval($act->duration) / 60;

        $careteam_info = [];
        $careteam_ids  = $patient->getCareTeam();
        if ( ! empty($careteam_ids) && is_array($careteam_ids)) {
            foreach ($careteam_ids as $id) {
                $careteam_info[$id] = User::find($id)->getFullName();
            }
        }

        $comment = $act->getActivityCommentFromMeta($actId);
        if ($comment) {
            $activity['comment'] = $comment;
        } else {
            $activity['comment'] = '';
        }

        $view_data = [
            'activity'      => $activity,
            'userTimeZone'  => $patient->timezone,
            'careteam_info' => $careteam_info,
            'patient'       => $patient,
            'program_id'    => $patient->program_id,
            'messages'      => $messages,
        ];

        return view('wpUsers.patient.activity.view', $view_data);
    }

    /**
     * @param bool $params
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(
        Request $request,
        $params = false
    ) {
        if ($params) {
            $input = $request->all();
        } else {
            if ($request->isJson()) {
                $input = $request->input();
            } else {
                return response('Unauthorized', 401);
            }
        }

        $nurseId = null;
        $patient = null;

        // convert minutes to seconds.
        if ($input['duration']) {
            $input['duration'] = $input['duration'] * 60;
            $client            = new Client();

            $nurseId   = $input['provider_id'];
            $patientId = $input['patient_id'];
            $duration  = (int) $input['duration'];

            $patient = User::find($patientId);

            if ($patient) {
                $isCcm        = $patient->isCcm();
                $isBehavioral = $patient->isBhi();
                if ($isCcm && $isBehavioral) {
                    $is_bhi = isset($input['is_behavioral'])
                        ? ('true' != $input['is_behavioral']
                            ? false
                            : true)
                        : false;
                    $input['is_behavioral'] = $is_bhi;
                } else {
                    $is_bhi                 = $isBehavioral;
                    $input['is_behavioral'] = $isBehavioral;
                }
            } else {
                throw new \Exception('patient_id '.$patientId.' does not correspond to any patient');
            }

            // Send a request to the time-tracking server to increment the start-time by the duration of the offline-time activity (in seconds)
            if ($nurseId && $patientId && $duration) {
                $url = config('services.ws.server-url').'/'.$nurseId.'/'.$patientId;
                try {
                    $timeParam = $is_bhi
                        ? 'bhiTime'
                        : 'ccmTime';
                    $res = $client->put(
                        $url,
                        [
                            'form_params' => [
                                'startTime' => $duration,
                                $timeParam  => $duration,
                            ],
                        ]
                    );
                    $status = $res->getStatusCode();
                    $body   = $res->getBody();
                    if (200 == $status) {
                        Log::info($body);
                    } else {
                        Log::critical($body);
                    }
                } catch (\Exception $ex) {
                    Log::critical($ex);
                }
            }
        }

        $activity = null;
        if ($patient) {
            /** @var ActivityService $activityService */
            $activityService            = app(ActivityService::class);
            $chargeableServicesDuration = $activityService->separateDurationForEachChargeableServiceId($patient, $input['duration'], $input['is_behavioral']);
            foreach ($chargeableServicesDuration as $chargeableServiceDuration) {
                $merged = array_merge($input, [
                    'duration'              => $chargeableServiceDuration->duration,
                    'chargeable_service_id' => $chargeableServiceDuration->id,
                ]);
                $activity = Activity::create($merged);
            }
        } else {
            $activity = Activity::create($input);
        }

        /** @var Nurse $nurse */
        $nurse = null;
        if ($nurseId) {
            $nurse = Nurse::whereUserId($nurseId)->first();
        }

        // store meta
        if ($activity && array_key_exists('meta', $input)) {
            $meta = $input['meta'];
            unset($input['meta']);
            $metaArray = [];
            $i         = 0;
            foreach ($meta as $actMeta) {
                $metaArray[$i] = new ActivityMeta($actMeta);

                ++$i;
            }

            $activity->meta()->saveMany($metaArray);
        }

        $performedAt = Carbon::parse($activity->performed_at);

        $this->activityService->processMonthlyActivityTime($input['patient_id'], $performedAt);
        event(new PatientActivityCreated($input['patient_id'], false));

        if ($nurse) {
            (new AlternativeCareTimePayableCalculator($nurse))
                ->adjustNursePayForActivity($activity);
        }

        return redirect()->route(
            'patient.activity.view',
            [
                'patientId' => $activity->patient_id,
                'actId'     => $activity->id,
            ]
        )->with(
            'messages',
            ['Successfully Created New Offline Activity']
        );
    }

    private function getActivityForPatient($patientId, $start, $end)
    {
        $acts = DB::table('lv_activities')
            ->select(
                DB::raw(
                    'lv_activities.id,lv_activities.logged_from,DATE(lv_activities.performed_at)as performed_at, lv_activities.type, SUM(lv_activities.duration) as duration, lv_activities.is_behavioral, users.first_name as provider_first_name, users.last_name as provider_last_name, users.suffix as provider_suffix'
                )
            )
            ->join('users', 'users.id', '=', 'lv_activities.provider_id')
            ->where('lv_activities.performed_at', '>=', $start)
            ->where('lv_activities.performed_at', '<=', $end)
            ->where('lv_activities.patient_id', $patientId)
            ->where(
                function ($q) {
                    $q->where('lv_activities.logged_from', 'activity')
                        ->orWhere('lv_activities.logged_from', 'manual_input')
                        ->orWhere('lv_activities.logged_from', 'pagetimer');
                }
            )
            ->groupBy(
                DB::raw(
                    'lv_activities.provider_id, DATE(lv_activities.performed_at),lv_activities.type,lv_activities.is_behavioral'
                )
            )
            ->orderBy('lv_activities.created_at', 'desc')
            ->get();

        $acts = json_decode(json_encode($acts), true);            //debug($acts);

        foreach ($acts as $key => $value) {
            $acts[$key]['provider_name'] = $this->getFullName(
                empty($acts[$key]['provider_first_name'])
                    ? ''
                    : $acts[$key]['provider_first_name'],
                empty($acts[$key]['provider_last_name'])
                    ? ''
                    : $acts[$key]['provider_last_name'],
                empty($acts[$key]['provider_suffix'])
                    ? ''
                    : $acts[$key]['provider_suffix']
            );
        }

        return $acts;
    }

    private function getFullName($firstName, $lastName, $suffix)
    {
        $firstName = ucwords(strtolower($firstName));
        $lastName  = ucwords(strtolower($lastName));

        return trim("${firstName} ${lastName} ${suffix}");
    }
}
