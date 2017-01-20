<?php namespace App\Http\Controllers\Patient;

use App\CareItem;
use App\CarePlan;
use App\CPRulesQuestions;
use App\Events\CarePlanWasApproved;
use App\Http\Controllers\Controller;
use App\Observation;
use App\PatientCareTeamMember;
use App\PhoneNumber;
use App\Practice;
use App\Services\CarePlanViewService;
use App\User;
use Carbon\Carbon;
use DB;
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

        return view('wpUsers.patient.dashboard', compact(['pendingApprovals']));
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
        if (!Auth::user()->can('observations-view')) {
            abort(403);
        }

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

        //dd($observation_json);
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

        $patientData = [];
        $patients = User::intersectPracticesWith(auth()->user())
            ->ofType('participant')
            ->with('primaryPractice')
            ->with('carePlan')
            ->with([
                'observations'    => function ($query) {
                    $query->where('obs_key', '!=', 'Outbound');
                    $query->orderBy('obs_date', 'DESC');
                    $query->first();
                },
                'careTeamMembers' => function ($q) {
                    $q->where('type', '=', PatientCareTeamMember::BILLING_PROVIDER)
                        ->with('user');
                },
                'phoneNumbers'    => function ($q) {
                    $q->where('type', '=', PhoneNumber::HOME);
                },
            ])
            ->get();


        $foundUsers = []; // save resources, no duplicate db calls
        $foundPrograms = []; // save resources, no duplicate db calls

        $isProvider = Auth::user()->hasRole('provider');
        $isCareCenter = Auth::user()->hasRole('care-center');
        $isAdmin = Auth::user()->hasRole('administrator');


        foreach ($patients as $patient) {
            // skip if patient has no name
            if (empty($patient->first_name)) {
                continue 1;
            }

            $careplanStatus = $patient->carePlan->status ?? '';
            $careplanStatusLink = '';
            $approverName = 'NA';
            $tooltip = 'NA';

            if ($careplanStatus == 'provider_approved') {
                $approver = $patient->carePlan->providerApproverUser;
                if ($approver) {
                    $approverName = $approver->fullName;
                }

                $carePlanProviderDate = $patient->carePlan->provider_date;
                $careplanStatus = 'Approved';
                $careplanStatusLink = '<span data-toggle="" title="' . $approverName . ' ' . $carePlanProviderDate . '">Approved</span>';
                $tooltip = $approverName . ' ' . $carePlanProviderDate;
            } else {
                if ($careplanStatus == 'qa_approved') {
                    $careplanStatus = 'Approve Now';
                    $tooltip = $careplanStatus;
                    $careplanStatusLink = 'Approve Now';
                    if ($isProvider) {
                        $careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.careplan.print',
                                ['patient' => $patient->id]) . '"><strong>Approve Now</strong></a>';
                    }
                } else {
                    if ($careplanStatus == 'draft') {
                        $careplanStatus = 'CLH Approve';
                        $tooltip = $careplanStatus;
                        $careplanStatusLink = 'CLH Approve';
                        if ($isCareCenter || $isAdmin) {
                            $careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show',
                                    ['patient' => $patient->id]) . '"><strong>CLH Approve</strong></a>';
                        }
                    }
                }
            }

            // get billing provider name
            $bpName = '';
            $bpID = $patient->billingProviderID;
            if (!isset($foundPrograms[$patient->program_id])) {
                $program = $patient->primaryPractice;
                $foundPrograms[$patient->program_id] = $program;
            } else {
                $program = $foundPrograms[$patient->program_id];
            }
            $programName = $program->display_name;

            $bpCareTeamMember = $patient->patientCareTeamMembers->first();

            if ($bpCareTeamMember) {
                $bpUser = $bpCareTeamMember->user;
                $bpName = $bpUser->fullName;
                $foundUsers[$bpID] = $bpUser;
            }

            // get date of last observation
            $lastObservationDate = 'No Readings';
            $lastObservation = $patient->observations;
            if ($lastObservation->count() > 0) {
                $lastObservationDate = date("m/d/Y", strtotime($lastObservation[0]->obs_date));
            }

            try {
                $patientData[] = [
                    'key'                        => $patient->id,
                    // $part->id,
                    'patient_name'               => $patient->fullName,
                    //$meta[$part->id]["first_name"][0] . " " .$meta[$part->id]["last_name"][0],
                    'first_name'                 => $patient->first_name,
                    //$meta[$part->id]["first_name"][0],
                    'last_name'                  => $patient->last_name,
                    //$meta[$part->id]["last_name"][0],
                    'ccm_status'                 => ucfirst($patient->ccmStatus),
                    //ucfirst($meta[$part->id]["ccm_status"][0]),
                    'careplan_status'            => $careplanStatus,
                    //$careplanStatus,
                    'tooltip'                    => $tooltip,
                    //$tooltip,
                    'careplan_status_link'       => $careplanStatusLink,
                    //$careplanStatusLink,
                    'careplan_provider_approver' => $approverName,
                    //$approverName,
                    'dob'                        => Carbon::parse($patient->birthDate)->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["birth_date"])),
                    'phone'                      => isset($patient->phoneNumbers->number)
                        ? $patient->phoneNumbers->number
                        : $patient->phone,
                    //$user_config[$part->id]["study_phone_number"],
                    'age'                        => $patient->age,
                    'reg_date'                   => Carbon::parse($patient->registrationDate)->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["registration_date"])) ,
                    'last_read'                  => $lastObservationDate,
                    //date("m/d/Y", strtotime($last_read)),
                    'ccm_time'                   => $patient->patientInfo->cur_month_activity_time,
                    //$ccm_time[0],
                    'ccm_seconds'                => $patient->patientInfo->cur_month_activity_time,
                    //$meta[$part->id]['cur_month_activity_time'][0]
                    'provider'                   => $bpName,
                    // $bpUserInfo['prefix'] . ' ' . $bpUserInfo['first_name'] . ' ' . $bpUserInfo['last_name'] . ' ' . $bpUserInfo['qualification']
                    'site'                       => $programName,
                ];
            } catch (\Exception $e) {
                \Log::critical("{$patient->id} has no patient info");
                \Log::critical("{$e} has no patient info");
            }
        }
        $patientJson = json_encode($patientData);

        return view('wpUsers.patient.listing', compact([
            'patientJson',
            'isProvider',
            'isCareCenter',
            'isAdmin',
        ]));
    }


    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function showPatientCarePlanPrintList(Request $request)
    {

        $patientData = [];
        $patients = User::intersectPracticesWith(auth()->user())
            ->with('phoneNumbers', 'patientInfo', 'careTeamMembers')->whereHas('roles', function ($q) {
                $q->where('name', '=', 'participant');
            })->get();
        if ($patients->count() > 0) {
            foreach ($patients as $patient) {
                // skip if patient has no name
                if (empty($patient->first_name)) {
                    continue 1;
                }
                // careplan status stuff from 2.x
                $careplanStatus = $patient->carePlanStatus;
                $careplanStatusLink = '';
                $approverName = 'NA';
                if ($patient->carePlanStatus == 'provider_approved') {
                    $approverId = $patient->carePlanProviderApprover;
                    $approver = User::find($approverId);
                    if ($approver) {
                        $approverName = $approver->fullName;
                        $careplanStatus = 'Approved';
                        $careplanStatusLink = '<span data-toggle="" title="' . $approver->fullName . ' ' . $patient->carePlanProviderDate . '">Approved</span>';
                        $tooltip = $approverName . ' ' . $patient->carePlanProviderDate;
                    }
                } else {
                    if ($patient->carePlanStatus == 'qa_approved') {
                        $careplanStatus = 'Approve Now';
                        $tooltip = $careplanStatus;
                        $careplanStatusLink = 'Approve Now';
                        if (Auth::user()->hasRole('provider')) {
                            $careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show',
                                    ['patient' => $patient->id]) . '"><strong>Approve Now</strong></a>';
                        }
                    } else {
                        if ($patient->carePlanStatus == 'draft') {
                            $careplanStatus = 'CLH Approve';
                            $tooltip = $careplanStatus;
                            $careplanStatusLink = 'CLH Approve';
                            if (Auth::user()->hasRole('care-center') || Auth::user()->hasRole('administrator')) {
                                $careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show',
                                        ['patient' => $patient->id]) . '"><strong>CLH Approve</strong></a>';
                            }
                        }
                    }
                }

                // get billing provider name
                $bpName = '';
                if (!empty($patient->billingProviderID)) {
                    $bpUser = User::find($patient->billingProviderID);
                    if ($bpUser) {
                        $bpName = $bpUser->fullName;
                    }
                }

                // get date of last observation
                $lastObservationDate = 'No Readings';
                $lastObservation = $patient->observations()->where('obs_key', '!=', 'Outbound')->orderBy('obs_date',
                    'DESC')->first();
                if (!empty($lastObservation)) {
                    $lastObservationDate = date("m/d/Y", strtotime($lastObservation->obs_date));
                }

                $patientData[] = [
                    'key'                        => $patient->id,
                    // $part->id,
                    'patient_name'               => $patient->fullName,
                    //$meta[$part->id]["first_name"][0] . " " .$meta[$part->id]["last_name"][0],
                    'first_name'                 => $patient->first_name,
                    //$meta[$part->id]["first_name"][0],
                    'last_name'                  => $patient->last_name,
                    //$meta[$part->id]["last_name"][0],
                    'ccm_status'                 => ucfirst($patient->ccmStatus),
                    //ucfirst($meta[$part->id]["ccm_status"][0]),
                    'careplan_status'            => $careplanStatus,
                    //$careplanStatus,
                    'tooltip'                    => $tooltip,
                    //$tooltip,
                    'careplan_status_link'       => $careplanStatusLink,
                    //$careplanStatusLink,
                    'careplan_provider_approver' => $approverName,
                    //$approverName,
                    'dob'                        => Carbon::parse($patient->birthDate)->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["birth_date"])),
                    'phone'                      => $patient->phone,
                    //$user_config[$part->id]["study_phone_number"],
                    'age'                        => $patient->age,
                    'reg_date'                   => Carbon::parse($patient->registrationDate)->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["registration_date"])) ,
                    'last_read'                  => $lastObservationDate,
                    //date("m/d/Y", strtotime($last_read)),
                    'ccm_time'                   => $patient->patientInfo->cur_month_activity_time,
                    //$ccm_time[0],
                    'ccm_seconds'                => $patient->patientInfo->cur_month_activity_time,
                    //$meta[$part->id]['cur_month_activity_time'][0]
                    'provider'                   => $bpName,
                    // $bpUserInfo['prefix'] . ' ' . $bpUserInfo['first_name'] . ' ' . $bpUserInfo['last_name'] . ' ' . $bpUserInfo['qualification']
                ];
            }
        }
        $patientJson = json_encode($patientData);

        return view('wpUsers.patient.carePlanPrintList', compact([
            'patientJson',
        ]));
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
        $query = $input['users'];

        $userIds = $commaList = implode(', ', Auth::user()->viewablePatientIds());

        $sql = "select distinct *
        	FROM users u
        	JOIN patient_info pi ON pi.user_id = u.id
             AND u.id IN (" . $userIds . ")
             AND concat(u.first_name , ' ', u.last_name, ' ', pi.user_id, ' ', pi.mrn_number, ' ', pi.birth_date ) like '%" . $query . "%'
             order by 1
            ;";

        $results = DB::select(DB::raw($sql));
        $patients = [];
        $i = 0;
        foreach ($results as $d) {
            $patients[$i]['name'] = ($d->display_name);
            $dob = new Carbon(($d->birth_date));
            $patients[$i]['dob'] = $dob->format('m-d-Y');
            $patients[$i]['mrn'] = $d->mrn_number;
            $patients[$i]['link'] = URL::route('patient.summary', ['patient' => $d->user_id]);

            $programObj = Practice::find($d->program_id);

            $patients[$i]['program'] = $programObj->display_name ?? '';
            $patients[$i]['hint'] = $patients[$i]['name'] . " DOB:" . $patients[$i]['dob'] . " [" . $patients[$i]['program'] . "] MRN: " . $patients[$i]['mrn'];
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
