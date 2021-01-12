<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Jobs\ProcessMonthltyPatientTime;
use CircleLinkHealth\Customer\Jobs\ProcessNurseMonthlyLogs;
use CircleLinkHealth\Customer\Reports\PatientDailyAuditReport;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\ActivityMeta;
use CircleLinkHealth\SharedModels\Entities\OfflineActivityTimeRequest;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use CircleLinkHealth\TimeTracking\Http\Requests\ShowPatientActivities;
use CircleLinkHealth\Timetracking\Requests\AdminCreateOfflineActivityTimeRequest;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use CircleLinkHealth\Timetracking\Services\TimeTrackerServerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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
            'program_id'         => $patient->program_id,
            'patient'            => $patient,
            'patient_name'       => $patient_name,
            'activity_types'     => Activity::input_activity_types(),
            'chargeableServices' => $this->getChargeableServices($patient->id),
            'provider_info'      => $provider_info,
            'userTimeZone'       => $userTimeZone,
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
                'table' => $acts,
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
        /** @var Activity $act */
        $act = Activity::with('chargeableService')->findOrFail($actId);

        if ($act->patient_id !== $patient->id) {
            abort(400, 'Not found');
        }

        //Set up note pack for view
        $activity                            = [];
        $messages                            = \Session::get('messages');
        $activity['type']                    = $act->type;
        $activity['chargeable_service_id']   = optional($act->chargeableService)->id;
        $activity['chargeable_service_name'] = optional($act->chargeableService)->display_name;
        $activity['performed_at']            = $act->performed_at;
        $activity['provider_name']           = User::find($act->provider_id)
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AdminCreateOfflineActivityTimeRequest $request)
    {
        $chargeableServiceId = $request->input('chargeable_service_id');
        $durationSeconds     = $request->input('duration_minutes') * 60;
        $nurseUserId         = $request->input('provider_id');
        $patientId           = $request->input('patient_id');

        /** @var User $patient */
        $patient = User::withTrashed()->find($patientId);

        /** @var ChargeableService $cs */
        $cs = ChargeableService::cached()->firstWhere('id', '=', $chargeableServiceId);

        /** @var Nurse $nurse */
        $nurse = Nurse::whereUserId($nurseUserId)->first();

        if ($nurse) {
            $this->syncWithTimeTrackerServer($nurseUserId, $patientId, $durationSeconds, $cs);
        }

        $metaData             = new ActivityMeta();
        $metaData->meta_key   = 'comment';
        $metaData->meta_value = $request->input('comment');

        $fakePageTimer                = new PageTimer();
        $fakePageTimer->activity_type = $request->input('type');
        $fakePageTimer->provider_id   = $nurseUserId;
        $fakePageTimer->start_time    = $request->input('performed_at');
        $fakePageTimer->patient_id    = $patientId;

        $chargeableServiceDuration = app(ActivityService::class)->getChargeableServiceIdDuration($patient, $durationSeconds, $cs->id);
        $activity                  = $this->processNewActivity($fakePageTimer, $chargeableServiceDuration, null !== $nurse, $metaData);

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
                    'lv_activities.id,lv_activities.logged_from,DATE(lv_activities.performed_at)as performed_at, lv_activities.type, SUM(lv_activities.duration) as duration, lv_activities.is_behavioral,lv_activities.chargeable_service_id,chargeable_services.display_name as chargeable_service_name,users.first_name as provider_first_name, users.last_name as provider_last_name, users.suffix as provider_suffix'
                )
            )
            ->join('users', 'users.id', '=', 'lv_activities.provider_id')
            ->leftJoin('chargeable_services', 'lv_activities.chargeable_service_id', '=', 'chargeable_services.id')
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
                    'lv_activities.provider_id, DATE(lv_activities.performed_at),lv_activities.type,lv_activities.chargeable_service_id'
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

    private function getChargeableServices($patientId)
    {
        return (new PatientServicesForTimeTracker((int) $patientId, now()))->get();
    }

    private function getFullName($firstName, $lastName, $suffix)
    {
        $firstName = ucwords(strtolower($firstName));
        $lastName  = ucwords(strtolower($lastName));

        return trim("${firstName} ${lastName} ${suffix}");
    }

    private function processNewActivity(PageTimer $pageTimer, ChargeableServiceDuration $chargeableServiceDuration, bool $isNurseUser, ActivityMeta $metaData): Activity
    {
        $activity = app(PatientServiceProcessorRepository::class)->createActivityForChargeableService('manual_input', $pageTimer, $chargeableServiceDuration);
        $activity->meta()->save($metaData);

        ProcessMonthltyPatientTime::dispatchNow($pageTimer->patient_id);
        if ($isNurseUser) {
            ProcessNurseMonthlyLogs::dispatchNow($activity);
        }
        event(new PatientActivityCreated($pageTimer->patient_id, false));

        return $activity;
    }

    private function syncWithTimeTrackerServer(int $nurseUserId, int $patientId, int $durationSeconds, ChargeableService $chargeableService)
    {
        $req                        = new OfflineActivityTimeRequest();
        $req->duration_seconds      = $durationSeconds;
        $req->requester_id          = $nurseUserId;
        $req->patient_id            = $patientId;
        $req->chargeable_service_id = $chargeableService->id;
        $req->setRelation('chargeableService', $chargeableService);

        app(TimeTrackerServerService::class)->syncOfflineTime($req);
    }
}
