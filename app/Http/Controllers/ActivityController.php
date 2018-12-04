<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Reports\PatientDailyAuditReport;
use App\Services\ActivityService;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * Class ActivityController
 * @package App\Http\Controllers
 */
class ActivityController extends Controller
{
    private $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function providerUIIndex(
        Request $request,
        $patientId
    ) {
        $patient = User::find($patientId);

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

        //downloads patient audit
        if ($request->ajax()) {
            $data = (new PatientDailyAuditReport($patient->patientInfo, Carbon::parse($start)))->renderPDF();

            return $data;
        }

        $acts = DB::table('lv_activities')
                  ->select(DB::raw('id,provider_id,logged_from,DATE(performed_at)as performed_at, type, SUM(duration) as duration'))
                  ->where('performed_at', '>=', $start)
                  ->where('performed_at', '<=', $end)
                  ->where('patient_id', $patientId)
                  ->where(function ($q) {
                      $q->where('logged_from', 'activity')
                        ->Orwhere('logged_from', 'manual_input')
                        ->Orwhere('logged_from', 'pagetimer');
                  })
                  ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                  ->orderBy('created_at', 'desc')
                  ->get();


        $acts = json_decode(json_encode($acts), true);            //debug($acts);


        foreach ($acts as $key => $value) {
            $provider = User::find($acts[$key]['provider_id']);
            if ($provider) {
                $acts[$key]['provider_name'] = $provider->getFullName();
            }
            unset($acts[$key]['provider_id']);
        }
        if ($acts) {
            $data = true;
        } else {
            $data = false;
        }

        $reportData = "data:" . json_encode($acts) . "";

        $years = [];
        for ($i = 0; $i < 3; $i++) {
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

    public function index(Request $request)
    {
        // display view
        $activities = Activity::orderBy('id', 'desc')->paginate(10);

        return view('activities.index', ['activities' => $activities]);
    }

    public function create(
        Request $request,
        $patientId
    ) {
        if (auth()->user()->hasRole('care-center') && ! in_array(app()->environment(), ['local', 'staging'])) {
            return abort(403);
        }

        if (! $patientId) {
            return abort(404);
        }

        $patient = User::find($patientId);

        if (! $patient) {
            return response("User not found", 401);
        }

        $patient_name = $patient->getFullName();

        $userTimeZone = $patient->timeZone;

        if (empty($userTimeZone)) {
            $userTimeZone = 'America/New_York';
        }

        $provider_info = User::ofType(['care-center', 'provider'])
                             ->intersectPracticesWith($patient)
                             ->orderBy('first_name')
                             ->get()
                             ->mapWithKeys(function ($user) {
                                 return [$user->id => $user->getFullName()];
                             })
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
                return response("Unauthorized", 401);
            }
        }//debug($request->all());

        // convert minutes to seconds.
        if ($input['duration']) {
            $input['duration'] = $input['duration'] * 60;

            $client = new Client();

            $nurseId   = $input['provider_id'];
            $patientId = $input['patient_id'];
            $duration  = (int)$input['duration'];

            $patient = User::find($patientId);

            if ($patient) {
                $isCcm        = $patient->isCcm();
                $isBehavioral = $patient->isBhi();
                if ($isCcm && $isBehavioral) {
                    $is_bhi                 = isset($input['is_behavioral'])
                        ? ($input['is_behavioral'] != 'true'
                            ? false
                            : true)
                        : false;
                    $input['is_behavioral'] = $is_bhi;
                } else {
                    $input['is_behavioral'] = $isBehavioral;
                }
            } else {
                throw new \Exception('patient_id ' . $patientId . ' does not correspond to any patient');
            }


            /**
             * Send a request to the time-tracking server to increment the start-time by the duration of the offline-time activity (in seconds)
             */
            if ($nurseId && $patientId && $duration) {
                $url = config('services.ws.server-url') . '/' . $nurseId . '/' . $patientId;
                try {
                    $timeParam = $is_bhi
                        ? 'bhiTime'
                        : 'ccmTime';
                    $res       = $client->put($url, [
                        'form_params' => [
                            'startTime' => $duration,
                            $timeParam  => $duration,
                        ],
                    ]);
                    $status    = $res->getStatusCode();
                    $body      = $res->getBody();
                    if ($status == 200) {
                        Log::info($body);
                    } else {
                        Log::critical($body);
                    }
                } catch (\Exception $ex) {
                    Log::critical($ex);
                }
            }
        }

        // store activity
        $actId = Activity::createNewActivity($input);

        $activity = Activity::find($actId);

        $nurse = null;

        if ($activity->provider_id) {
            $nurse = User::find($activity->provider_id)->nurseInfo;
        }

        // store meta
        if (array_key_exists('meta', $input)) {
            $meta = $input['meta'];
            unset($input['meta']);
            $metaArray = [];
            $i         = 0;
            foreach ($meta as $actMeta) {
                $metaArray[$i] = new ActivityMeta($actMeta);

                $i++;
            }

            $activity->meta()->saveMany($metaArray);
        }

        $performedAt = Carbon::parse($activity->performed_at);

        $this->activityService->processMonthlyActivityTime($input['patient_id'], $performedAt);

        if ($nurse) {
            $computer = new AlternativeCareTimePayableCalculator($nurse);
            $computer->adjustNursePayForActivity($activity);
        }

        return redirect()->route('patient.activity.view', [
            'patient' => $activity->patient_id,
            'actId'   => $activity->id,
        ])->with(
            'messages',
            ['Successfully Created New Offline Activity']
        );
    }

    public function show(
        Request $input,
        $patientId,
        $actId
    ) {
        $patient = User::find($patientId);
        $act     = Activity::find($actId);
        //Set up note pack for view
        $activity                  = [];
        $messages                  = \Session::get('messages');
        $activity['type']          = $act->type;
        $activity['performed_at']  = $act->performed_at;
        $activity['provider_name'] = User::find($act->provider_id)
            ? (User::find($act->provider_id)->getFullName())
            : '';
        $activity['duration']      = intval($act->duration) / 60;

        $careteam_info = [];
        $careteam_ids  = $patient->getCareTeam();
        if ((@unserialize($careteam_ids) !== false)) {
            $careteam_ids = unserialize($careteam_ids);
        }
        if (! empty($careteam_ids) && is_array($careteam_ids)) {
            foreach ($careteam_ids as $id) {
                $careteam_info[$id] = User::find($id)->getFullName();
                ;
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
            'userTimeZone'  => $patient->timeZone,
            'careteam_info' => $careteam_info,
            'patient'       => $patient,
            'program_id'    => $patient->program_id,
            'messages'      => $messages,
        ];

        return view('wpUsers.patient.activity.view', $view_data);
    }

    public function update(Request $request)
    {
        if ($request->isJson()) {
            $input = $request->input();
        } else {
            if ($request->isMethod('POST')) {
                if ($request->header('Client') == 'ui') { // WP Site
                    $input = json_decode(Crypt::decrypt($request->input('data')), true);
                }
            } else {
                return response("Unauthorized", 401);
            }
        }

        //  Check if there are any meta nested parts in the incoming request
        $meta = $input['meta'];
        unset($input['meta']);

        $activity = Activity::find($input['activity_id']);
        $activity->fill($input)->save();

        $actMeta = ActivityMeta::where('activity_id', $input['activity_id'])->where(
            'meta_key',
            $meta['0']['meta_key']
        )->first();
        $actMeta->fill($meta['0'])->save();

        $this->activityService->processMonthlyActivityTime([$input['patient_id']]);

        return response("Activity Updated", 201);
    }

    public function destroy($id)
    {
        //
    }
}
