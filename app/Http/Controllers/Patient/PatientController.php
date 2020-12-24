<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient;

use CircleLinkHealth\Core\Contracts\ReportFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CallPatientRequest;
use App\Http\Requests\ContactDetailsRequest;
use App\Http\Requests\MarkPrimaryPhoneRequest;
use CircleLinkHealth\SharedModels\Services\Observations\ObservationConstants;
use App\Testing\CBT\TestPatients;
use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\SeesAutoQAButton;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use CircleLinkHealth\Customer\Services\NurseCalendarService;
use CircleLinkHealth\PdfService\Services\PdfService;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientController extends Controller
{
    private $formatter;
    /**
     * @var NurseCalendarService
     */
    private $fullCalendarService;

    /**
     * PatientController constructor.
     */
    public function __construct(ReportFormatter $formatter, NurseCalendarService $fullCalendarService)
    {
        $this->formatter           = $formatter;
        $this->fullCalendarService = $fullCalendarService;
    }

    public function createCBTTestPatient(Request $request)
    {
        if (isProductionEnv()) {
            return back();
        }

        return view('patient.create-test-patients');
    }

    public function markAsPrimaryPhone(MarkPrimaryPhoneRequest $request)
    {
        $newPrimaryPhoneId = $request->input('phoneId');

        /** @var User $patientUser */
        $patientUser = $request->get('patientUser');

        $this->unsetCurrentPrimaryNumbers($patientUser);

        $patientUser->phoneNumbers()
            ->where('id', $newPrimaryPhoneId)->update(
                [
                    'is_primary' => true,
                ]
            );

        return $this->ok();
    }

    public function patientAjaxSearch(Request $request)
    {
        return view('wpUsers.patient.select');
    }

    /**
     * @return mixed
     */
    public static function phoneNumbersFor(User $user)
    {
        return $user->phoneNumbers
            ->filter(function ($phone) {
                return ! empty($phone->number);
            });
    }

    /**
     * Process the specified resource.
     *
     * @return Response
     */
    public function processPatientSelect(Request $request)
    {
        $params = $request->all();
        if ( ! empty($params)) {
            if (isset($params['findUser'])) {
                $user = User::find($params['findUser']);
                if ($user) {
                    return redirect()->route('patient.summary', [$params['findUser']]);
                }
            }
        }

        //route not found
        return redirect()->route('patient.dashboard', [$params['findUser']]);
    }

    public function queryPatient(Request $request)
    {
        $input = $request->all();

        if ( ! array_key_exists('users', $input)) {
            return response()->json([], 400);
        }

        $searchTerms = explode(' ', $input['users']);

        $query = User::intersectPracticesWith(auth()->user())
            ->ofType('participant')
            ->with(['primaryPractice', 'patientInfo', 'phoneNumbers']);

        foreach ($searchTerms as $term) {
            $query->where(function ($q) use ($term) {
                $phoneNumberTerm = extractNumbers($term);

                $q->where('first_name', 'like', "%${term}%")
                    ->orWhere('last_name', 'like', "%${term}%")
                    ->orWhere('display_name', 'like', "%${term}%")
                    ->orWhere('id', 'like', "%${term}%")
                    ->orWhereHas('patientInfo', function ($query) use ($term) {
                        $query->where('mrn_number', 'like', "%${term}%")
                            ->orWhere('birth_date', 'like', "%${term}%");
                    })
                    ->when( ! empty($phoneNumberTerm), function ($q) use ($phoneNumberTerm) {
                        $q->orWhereHas('phoneNumbers', function ($query) use ($phoneNumberTerm) {
                            $query->where('number', 'like', "%${phoneNumberTerm}%");
                        });
                    });
            });
        }

        $results  = $query->get();
        $patients = [];
        $i        = 0;
        foreach ($results as $d) {
            $patients[$i]['name'] = ($d->display_name);
            $dob                  = new Carbon(($d->getBirthDate()));
            $patients[$i]['dob']  = $dob->format('m-d-Y');
            $patients[$i]['mrn']  = $d->getMRN();
            $patients[$i]['link'] = auth()->user()->isCallbacksAdmin()
                ? route('patient.schedule.activity', [$d->program_id, $d->id])
                : route('patient.summary', ['patientId' => $d->id]);

            $programObj = Practice::find($d->program_id);

            $patients[$i]['program'] = $programObj->display_name ?? '';
            $patients[$i]['hint']    = $patients[$i]['name'].' DOB:'.$patients[$i]['dob'].' ['.$patients[$i]['program']."] MRN: {$patients[$i]['mrn']} ID: {$d->id} PRIMARY PHONE: {$d->getPrimaryPhone()}";
            ++$i;
        }

        return response()->json($patients);
    }

    public function saveNewAgentPhoneNumber(ContactDetailsRequest $request)
    {
        $altPhoneNumber = $request->input('phoneNumber');
        $altPhoneNumber = formatPhoneNumberE164($altPhoneNumber);
        /** @var User $patientUser */
        $patientUser = $request->get('patientUser');

        $patientUser->patientInfo->update(
            [
                'agent_name'         => $request->input('agentName'),
                'agent_email'        => $request->input('agentEmail'),
                'agent_telephone'    => strtolower($altPhoneNumber),
                'agent_relationship' => $request->input('agentRelationship'),
            ]
        );

        return response()->json([
            'message' => 'Agent phone number has been saved!',
        ], 200);
    }

    public function saveNewPhoneNumber(ContactDetailsRequest $request)
    {
        $phoneType   = $request->input('phoneType');
        $phoneNumber = $request->input('phoneNumber');
        $userId      = $request->input('patientUserId');

        if ( ! allowNonUsPhones()) {
            $phoneNumber = formatPhoneNumberE164($phoneNumber);
        }

        /** @var User $patientUser */
        $patientUser = $request->get('patientUser');
        $locationId  = $request->get('locationId');

        if ($request->input('makePrimary')) {
            $this->unsetCurrentPrimaryNumbers($patientUser);
        }

        /** @var PhoneNumber $newPhoneNumber */
        $newPhoneNumber = $patientUser->phoneNumbers()->updateOrCreate(
            [
                'user_id' => $userId,
                'type'    => strtolower($phoneType),
            ],
            [
                'number'      => $phoneNumber,
                'is_primary'  => $request->input('makePrimary'),
                'location_id' => $locationId,
            ]
        );

        return response()->json([
            'message' => 'Phone number has been saved!',
        ], 200);
    }

    public function scheduleActivity($practiceId, $patientId)
    {
        $practice  = Practice::findOrFail($practiceId);
        $patient   = User::findOrFail($patientId);
        $careCoach = optional(app(NurseFinderEloquentRepository::class)->assignedNurse($patientId))->permanentNurse;

        return view('patient.schedule-task', [
            'practiceId'    => $practice->id,
            'practiceName'  => $practice->display_name,
            'patientId'     => $patient->id,
            'patientName'   => $patient->getFullName(),
            'careCoachId'   => optional($careCoach)->id,
            'careCoachName' => optional($careCoach)->getFullName(),
        ]);
    }

    public function showCallPatientPage(CallPatientRequest $request, $patientId)
    {
        /** @var User $user */
        $user = User::with('phoneNumbers')
            ->with('patientInfo.location')
            ->with('primaryPractice.locations')
            ->where('id', $patientId)
            ->firstOrFail();

        $clinicalEscalationNumber = null;
        if (optional($user->patientInfo->location)->clinical_escalation_phone) {
            $clinicalEscalationNumber = $user->patientInfo->location->clinical_escalation_phone;
        }

        if ( ! $clinicalEscalationNumber && $user->primaryPractice) {
            $practicePrimaryLocation = $user->primaryPractice->primaryLocation();
            if ($practicePrimaryLocation) {
                $clinicalEscalationNumber = $practicePrimaryLocation->clinical_escalation_phone;
            }
        }

        //naive authentication for the CPM Caller Service
        $cpmToken = \Hash::make(config('app.key').Carbon::today()->toDateString());

        return view('wpUsers.patient.calls.index')
            ->with([
                'patient'                  => $user,
                'clinicalEscalationNumber' => $clinicalEscalationNumber,
                'cpmToken'                 => $cpmToken,
                'allowNonUsPhones'         => allowNonUsPhones(),
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function showDashboard()
    {
        $pendingApprovals = 0;

        $nurse                          = null;
        $showPatientsPendingApprovalBox = false;
        $seesAutoApprovalButton         = false;

        /** @var User $user */
        $user = auth()->user();

        if ($user->isCareCoach() && $user->nurseInfo) {
            $nurse = $user->nurseInfo;
        }

        if ($user->canApproveCarePlans()) {
            $showPatientsPendingApprovalBox = true;
            $pendingApprovals               = User::patientsPendingProviderApproval($user)->count();
        } elseif ($user->isAdmin() && $user->canQAApproveCarePlans()) {
            $showPatientsPendingApprovalBox = true;
            $pendingApprovals               = User::patientsPendingCLHApproval($user)->count();
            $seesAutoApprovalButton         = SeesAutoQAButton::userId(auth()->id());
        }

        $noLiveCountTimeTracking = true;
        $authData                = $this->fullCalendarService->getAuthData();

        return view(
            'wpUsers.patient.dashboard',
            compact([
                'pendingApprovals',
                'nurse',
                'showPatientsPendingApprovalBox',
                'noLiveCountTimeTracking',
                'authData',
                'seesAutoApprovalButton',
            ])
        );
    }

    /**
     * Display Alerts.
     *
     * @param int $patientId
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
            if ( ! $wpUser) {
                return response('User not found', 401);
            }
        }

        return view('wpUsers.patient.alerts', ['patient' => $wpUser]);
    }

    /**
     * Display the specified resource.
     *
     * @return Application|Factory|Response|View
     */
    public function showPatientListing()
    {
        if (auth()->user()->isCareCoach()) {
            abort(403);
        }

        return view('wpUsers.patient.listing');
    }

    public function showPatientListingPdf(Request $request, PdfService $pdfService)
    {
        if (auth()->user()->isCareCoach()) {
            abort(403);
        }

        $showPracticePatientsInput = $request->input('showPracticePatients', null);
        $isProvider                = auth()->user()->isProvider();
        $showPracticePatients      = true;
        $carePlanStatus            = null;
        if ($isProvider) {
            // CPM-1790, non-admins should only see rn_approved, and provider_approved
            $carePlanStatus = [CarePlan::PROVIDER_APPROVED, CarePlan::RN_APPROVED];
            if (User::SCOPE_LOCATION === auth()->user()->scope || 'false' === $showPracticePatientsInput) {
                $showPracticePatients = false;
            }
        }

        $storageDirectory = 'storage/pdfs/patients/';
        $datetimePrefix   = date('Y-m-dH:i:s');
        $fileName         = $storageDirectory.$datetimePrefix.'-patient-list.pdf';
        $file             = $pdfService->createPdfFromView('wpUsers.patient.listing-pdf', [
            'patients' => $this->formatter->patients(null, $showPracticePatients, $carePlanStatus),
        ], null, [
            'orientation'  => 'Landscape',
            'margin-left'  => '3',
            'margin-right' => '3',
        ]);

        return response()->file($file);
    }

    /**
     * Display Notes.
     *
     * @param bool $patientId
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
            if ( ! $wpUser) {
                return response('User not found', 401);
            }
            // program
            $program = Practice::find($wpUser->program_id);
        }

        // program view

        return view('wpUsers.patient.notes', [
            'program' => $program,
            'patient' => $wpUser,
        ]);
    }

    /**
     * Display Observation Create.
     *
     * @param int $patientId
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
            if ( ! $patient) {
                return response('User not found', 401);
            }
        }

        //leave it here?
        // security
        if ( ! Auth::user()->hasPermissionForSite('observation.create', $patient->getPrimaryPracticeId())) {
            abort(403);
        }

        return view('wpUsers.patient.observation.create', [
            'patient'                  => $patient,
            'acceptedObservationTypes' => collect(ObservationConstants::ACCEPTED_OBSERVATION_TYPES)->sortBy('display_name'),
            'observationCatecories'    => collect(ObservationConstants::CATEGORIES)->sortBy('display_name'),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return Application|Factory|Response|View
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

    /**
     * Display the specified resource.
     *
     * @param int $patientId
     *
     * @return Response
     */
    public function showPatientSummary(
        Request $request,
        $patientId
    ) {
        $patientUser = User::with([
            'primaryPractice',
            'ccdProblems' => function ($q) {
                $q->with('cpmProblem.cpmInstructions')
                    ->whereNotNull('cpm_problem_id');
            },
            'observations' => function ($q) {
                $q->where('obs_unit', '!=', 'invalid')
                    ->where('obs_unit', '!=', 'scheduled')
                    ->whereNotNull('obs_value')
                    ->where('obs_value', '!=', '')
                    ->orderBy('obs_date', 'desc')
                    ->take(40);
            },
            'patientSummaries',
        ])
            ->where('id', $patientId)
            ->firstOrFail();

        $detailSection = $request->input('detail', '');

        $observations = $patientUser->observations;

        // build array of pcp
        $obs_by_pcp = [
            'obs_biometrics'  => [],
            'obs_medications' => [],
            'obs_symptoms'    => [],
            'obs_lifestyle'   => [],
        ];
        foreach ($observations as $observation) {
            if ('' == $observation['obs_value']) {
                //$obs_date = date_create($observation['obs_date']);
                //if( (($obs_date->format('Y-m-d')) < date("Y-m-d")) && $observation['obs_key'] == 'Call' ) {
                if ('Call' != $observation['obs_key']) { // skip NR's, which are any obs that has no value (other than call)
                    continue;
                }
            }
            $observation['parent_item_text'] = '---';
            switch ($observation['obs_key']) {
                case ObservationConstants::A1C:
                    $observation['description']     = ObservationConstants::A1C;
                    $obs_by_pcp['obs_biometrics'][] = $this->prepareForWebix($observation);
                    break;
                case 'Cigarettes':
                case ObservationConstants::CIGARETTE_COUNT:
                    if ($description = ObservationConstants::ACCEPTED_OBSERVATION_TYPES[$observation->obs_message_id]['display_name'] ?? null) {
                        $observation['description'] = $description;
                    }
                    $obs_by_pcp['obs_biometrics'][] = $this->prepareForWebix($observation);
                    break;
                case 'Blood_Pressure':
                case 'Blood_Sugar':
                case ObservationConstants::BLOOD_PRESSURE:
                case ObservationConstants::BLOOD_SUGAR:
                case ObservationConstants::WEIGHT:
                    $observation['description']     = $observation['obs_key'];
                    $obs_by_pcp['obs_biometrics'][] = $this->prepareForWebix($observation);
                    break;
                case ObservationConstants::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE:
                    if ($description = ObservationConstants::ACCEPTED_OBSERVATION_TYPES[$observation->obs_message_id]['display_name'] ?? null) {
                        $observation['description'] = $description;
                    }
                    $obs_by_pcp['obs_medications'][] = $this->prepareForWebix($observation);
                    break;
                case ObservationConstants::SYMPTOMS_OBSERVATION_TYPE:
                    if ($description = ObservationConstants::ACCEPTED_OBSERVATION_TYPES[$observation->obs_message_id]['display_name'] ?? null) {
                        $observation['items_text']  = $description;
                        $observation['description'] = $description;
                    }
                    $obs_by_pcp['obs_symptoms'][] = $this->prepareForWebix($observation);
                    break;
                case 'Other':
                case ObservationConstants::LIFESTYLE_OBSERVATION_TYPE:
                    if ($description = ObservationConstants::ACCEPTED_OBSERVATION_TYPES[$observation->obs_message_id]['display_name'] ?? null) {
                        $observation['description'] = $description;
                    }
                    $obs_by_pcp['obs_lifestyle'][] = $this->prepareForWebix($observation);
                    break;
                default:
                    break;
            }
        }

        return view('wpUsers.patient.summary', [
            'program'          => $patientUser->primaryPractice,
            'patient'          => $patientUser,
            'wpUser'           => $patientUser,
            'detailSection'    => $detailSection,
            'observation_data' => collect($obs_by_pcp)->transform(function ($obsType) {
                return json_encode($obsType);
            }),
            'problems' => $patientUser->getProblemsToMonitor(),
            'filter'   => '',
            'sections' => [
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
            ],
        ]);
    }

    /**
     * Select Program.
     *
     * @param bool $patientId
     *
     * @return Application|Response|ResponseFactory
     */
    public function showSelectProgram(
        Request $request,
        $patientId = false
    ) {
        $wpUser = [];
        if ($patientId) {
            $wpUser = User::find($patientId);
            if ( ! $wpUser) {
                return response('User not found', 401);
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
     * Create Cross Browser Testing Patients.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCBTTestPatient(Request $request)
    {
        if (isProductionEnv()) {
            return back();
        }

        (new TestPatients())->create();

        return redirect()->back();
    }

    private function getAllNumbersOfPatient(int $patientUserId)
    {
        return PhoneNumber::whereUserId($patientUserId)->get();
    }

    /**
     * @return bool
     */
    private function hasOtherPrimaryNumbers(User $patientUser)
    {
        return $patientUser->phoneNumbers()
            ->where('is_primary', true)->count() > 0;
    }

    private function prepareForWebix($observation)
    {
        return [
            'obs_key'     => $observation->obs_key,
            'description' => str_replace(
                '_',
                ' ',
                $observation->description
            ),
            'obs_value'      => $observation->obs_value,
            'dm_alert_level' => empty($alertLevel = $observation->getAlertLevel()) ? 'default' : $alertLevel,
            'obs_unit'       => $observation->obs_unit,
            'obs_message_id' => $observation->obs_message_id,
            'comment_date'   => Carbon::parse($observation->obs_date)->format('m-d-y h:i:s A'),
        ];
    }

    private function unsetCurrentPrimaryNumbers(User $patientUser)
    {
        $patientUser->phoneNumbers()
            ->where('is_primary', true)
            ->update(
                [
                    'is_primary' => false,
                ]
            );
    }
}
