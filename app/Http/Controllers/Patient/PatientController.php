<?php namespace App\Http\Controllers\Patient;

use App\CareItem;
use App\CarePerson;
use App\CarePlan;
use App\CPRulesQuestions;
use App\Events\CarePlanWasApproved;
use App\Formatters\WebixFormatter;
use App\Http\Controllers\Controller;
use App\Observation;
use App\PhoneNumber;
use App\Practice;
use App\Services\CarePlanViewService;
use App\User;
use Carbon\Carbon;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use URL;

class PatientController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function showDashboard(Request $request)
    {
        $pendingApprovals = CarePlan::getNumberOfCareplansPendingApproval(auth()->user());

        $nurse = null;

        if (auth()->user()->nurseInfo && auth()->user()->hasRole(['care-center'])) {
            $nurse = auth()->user()->nurseInfo;
            $nurse->nurseInfo->workhourables()->firstOrCreate([]);
        }

        if (auth()->user()->providerInfo && auth()->user()->hasRole(['provider'])) {
            $patients = auth()->user()->patientsPendingApproval();
            $providerApprovals = (new WebixFormatter())->patientListing($patients);
        }

        return view('wpUsers.patient.dashboard', compact(['pendingApprovals', 'nurse']));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showPatientSummary(
        Request $request,
        $patientId,
        CarePlanViewService $carePlanViewService
    ) {
        $messages = \Session::get('messages');

        $wpUser = User::find($patientId);
        if (!$wpUser) {
            return response("User not found", 401);
        }

        // security
//        if (!Auth::user()->can('observations-view')) {
//            abort(403);
//        }

        // program
        $program = Practice::find($wpUser->program_id);

        $problems = $carePlanViewService->getProblemsToMonitor($wpUser);

        $params = $request->all();
        $detailSection = '';
        if (isset($params['detail'])) {
            $detailSection = $params['detail'];
        }

        $sections = [
            [
                'section'           => 'obs_biometrics',
                'id'                => 'obs_biometrics_dtable',
                'title'             => 'Biometrics',
                'col_name_question' => 'Reading Type',
                'col_name_severity' => 'Reading',
            ],
            [
                'section'           => 'obs_medications',
                'id'                => 'obs_medications_dtable',
                'title'             => 'Medications',
                'col_name_question' => 'Medication',
                'col_name_severity' => 'Adherence',
            ],
            [
                'section'           => 'obs_symptoms',
                'id'                => 'obs_symptoms_dtable',
                'title'             => 'Symptoms',
                'col_name_question' => 'Symptom',
                'col_name_severity' => 'Severity',
            ],
            [
                'section'           => 'obs_lifestyle',
                'id'                => 'obs_lifestyle_dtable',
                'title'             => 'Lifestyle',
                'col_name_question' => 'Question',
                'col_name_severity' => 'Response',
            ],
        ];

        $observations = Observation::where('user_id', '=', $wpUser->id)
            ->where('obs_unit', '!=', "invalid")
            ->where('obs_unit', '!=', "scheduled")
            ->orderBy('obs_date', 'desc')
            ->take(100)
            ->get();

        // build array of pcp
        $obs_by_pcp = [
            'obs_biometrics'  => [],
            'obs_medications' => [],
            'obs_symptoms'    => [],
            'obs_lifestyle'   => [],
        ];
        foreach ($observations as $observation) {
            if ($observation['obs_value'] == '') {
                //$obs_date = date_create($observation['obs_date']);
                //if( (($obs_date->format('Y-m-d')) < date("Y-m-d")) && $observation['obs_key'] == 'Call' ) {
                if ($observation['obs_key'] != 'Call') { // skip NR's, which are any obs that has no value (other than call)
                    continue 1;
                }
            }
            $observation['parent_item_text'] = '---';
            switch ($observation["obs_key"]) {
                case 'A1c':
                    $observation['description'] = 'A1c';
                    $obs_by_pcp['obs_biometrics'][] = $observation;
                    break;
                case 'HSP_ER':
                case 'HSP_HOSP':
                    break;
                case 'Cigarettes':
                    $observation['description'] = 'Smoking (# per day)';
                    $obs_by_pcp['obs_biometrics'][] = $observation;
                    break;
                case 'Blood_Pressure':
                case 'Blood_Sugar':
                case 'Weight':
                    $observation['description'] = $observation["obs_key"];
                    $obs_by_pcp['obs_biometrics'][] = $observation;
                    break;
                case 'Adherence':
                    $question = CPRulesQuestions::where('msg_id', '=', $observation->obs_message_id)->first();
                    // find carePlanItem with qid
                    if ($question) {
                        $item = CareItem::where('qid', '=', $question->qid)->first();
                        if ($item) {
                            $observation['description'] = $item->display_name;
                        }
                    }
                    $obs_by_pcp['obs_medications'][] = $observation;
                    break;
                case 'Symptom':
                case 'Severity':
                    // get description
                    $question = CPRulesQuestions::where('msg_id', '=', $observation->obs_message_id)->first();
                    if ($question) {
                        $observation['items_text'] = $question->description;
                        $observation['description'] = $question->description;
                        $observation['obs_key'] = $question->description;
                    }
                    $obs_by_pcp['obs_symptoms'][] = $observation;
                    break;
                case 'Other':
                case 'Call':
                    // only y/n responses, skip anything that is a number as its assumed it is response to a list
                    $question = CPRulesQuestions::where('msg_id', '=', $observation->obs_message_id)->first();
                    // find carePlanItem with qid
                    if ($question) {
                        $item = CareItem::where('qid', '=', $question->qid)->first();
                        if ($item) {
                            $observation['description'] = $item->display_name;
                        }
                    }
                    if (($observation['obs_key'] == 'Call') || (!is_numeric($observation['obs_value']))) {
                        $obs_by_pcp['obs_lifestyle'][] = $observation;
                    }
                    break;
                default:
                    break;
            }
        }

        $observation_json = [];
        foreach ($obs_by_pcp as $section => $observations) {
            $o = 0;
            $observation_json[$section] = "data:[";
            foreach ($observations as $observation) {
                // limit to 3 if not detail
                if (empty($detailSection)) {
                    if ($o >= 3) {
                        continue 1;
                    }
                }
                // set default
                $alertLevel = 'default';
                if (!empty($observation->alert_level)) {
                    $alertLevel = $observation->alert_level;
                }
                // lastly format json
                $observation_json[$section] .= "{ obs_key:'" . $observation->obs_key . "', " .
                    "description:'" . str_replace('_', " ", $observation->description) . "', " .
                    "obs_value:'" . $observation->obs_value . "', " .
                    "dm_alert_level:'" . $alertLevel . "', " .
                    "obs_unit:'" . $observation->obs_unit . "', " .
                    "obs_message_id:'" . $observation->obs_message_id . "', " .
                    "comment_date:'" . Carbon::parse($observation->obs_date)->format('m-d-y h:i:s A') . "', " . "},";
                $o++;
            }
            $observation_json[$section] .= "],";
        }

        //return response()->json($cpFeed);
        return view('wpUsers.patient.summary', [
            'program'          => $program,
            'patient'          => $wpUser,
            'wpUser'           => $wpUser,
            'sections'         => $sections,
            'detailSection'    => $detailSection,
            'observation_data' => $observation_json,
            'messages'         => $messages,
            'problems'         => $problems,
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function showPatientListing(Request $request)
    {
        //If returning back from after approving a careplan from /view-careplan,
        //then update the status.
        $input = $request->all();

        if (isset($input['patient_approval_id'])) {
            event(new CarePlanWasApproved(User::find($input['patient_approval_id'])));
        }

        $data = (new WebixFormatter())->patientListing();

        return view('wpUsers.patient.listing', $data);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function showPatientSelect(Request $request)
    {
        // get number of approvals
        $patients = User::intersectPracticesWith(auth()->user())
            ->with('phoneNumbers', 'patientInfo', 'careTeamMembers')->whereHas('roles', function ($q) {
                $q->where('name', '=', 'participant');
            })->get()->pluck('fullNameWithId', 'id')->all();

        return view('wpUsers.patient.select', compact(['patients']));
    }

    public function queryPatient(Request $request)
    {

        $input = $request->all();

        if (!array_key_exists('users', $input)) {
            return;
        }

        $searchTerms = explode(' ', $input['users']);

        $query = User::intersectPracticesWith(auth()->user())
            ->ofType('participant')
            ->with('primaryPractice')
            ->with('patientInfo');

        foreach ($searchTerms as $term) {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%$term%")
                    ->orWhere('last_name', 'like', "%$term%")
                    ->orWhere('id', 'like', "%$term%")
                    ->orWhereHas('patientInfo', function($query) use ($term) {
                        $query->where('mrn_number', 'like', "%$term%")
                            ->orWhere('birth_date', 'like', "%$term%");
                    });
            });
        }

        $results = $query->get();
        $patients = [];
        $i = 0;
        foreach ($results as $d) {
            $patients[$i]['name'] = ($d->display_name);
            $dob = new Carbon(($d->birth_date));
            $patients[$i]['dob'] = $dob->format('m-d-Y');
            $patients[$i]['mrn'] = $d->mrn_number;
            $patients[$i]['link'] = URL::route('patient.summary', ['patient' => $d->id]);

            $programObj = Practice::find($d->program_id);

            $patients[$i]['program'] = $programObj->display_name ?? '';
            $patients[$i]['hint'] = $patients[$i]['name'] . " DOB:" . $patients[$i]['dob'] . " [" . $patients[$i]['program'] . "] MRN: {$patients[$i]['mrn']} ID: {$d->id}";
            $i++;
        }

        return response()->json($patients);
    }

    public function patientAjaxSearch(Request $request)
    {

        return view('wpUsers.patient.select');
    }

    /**
     * Process the specified resource.
     *
     * @return Response
     */
    public function processPatientSelect(Request $request)
    {
        $params = $request->all();
        if (!empty($params)) {
            if (isset($params['findUser'])) {
                $user = User::find($params['findUser']);
                if ($user) {
                    return redirect()->route('patient.summary', [$params['findUser']]);
                }
            }
        }

        return redirect()->route('patient.dashboard', [$params['findUser']]);
    }

    /**
     * Select Program
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showSelectProgram(
        Request $request,
        $patientId = false
    ) {
        $wpUser = [];
        if ($patientId) {
            $wpUser = User::find($patientId);
            if (!$wpUser) {
                return response("User not found", 401);
            }
        }

        // program
        $program = Practice::find($wpUser->program_id);

        return view('wpUsers.patient.alerts', [
            'program' => $program,
            'patient' => $wpUser,
        ]);
    }


    /**
     * Display Alerts
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showPatientAlerts(
        Request $request,
        $patientId = false
    ) {
        $wpUser = [];
        if ($patientId) {
            $wpUser = User::find($patientId);
            if (!$wpUser) {
                return response("User not found", 401);
            }
        }

        return view('wpUsers.patient.alerts', ['patient' => $wpUser]);
    }


    /**
     * Display Notes
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showPatientNotes(
        Request $request,
        $patientId = false
    ) {
        $wpUser = [];
        if ($patientId) {
            // patient view
            $wpUser = User::find($patientId);
            if (!$wpUser) {
                return response("User not found", 401);
            }
            // program
            $program = Practice::find($wpUser->program_id);
        } else {
            // program view
        }

        return view('wpUsers.patient.notes', [
            'program' => $program,
            'patient' => $wpUser,
        ]);
    }


    /**
     * Display Observation Create
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showPatientObservationCreate(
        Request $request,
        $patientId = false
    ) {
        $patient = [];
        if ($patientId) {
            $patient = User::find($patientId);
            if (!$patient) {
                return response("User not found", 401);
            }
        }

        // security
        if (!Auth::user()->can('observations-create')) {
            abort(403);
        }

        return view('wpUsers.patient.observation.create', ['patient' => $patient]);
    }
}
