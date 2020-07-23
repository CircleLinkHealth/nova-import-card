<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient;

use App\Contracts\ReportFormatter;
use App\FullCalendar\NurseCalendarService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CallPatientRequest;
use App\Services\Observations\ObservationConstants;
use App\Testing\CBT\TestPatients;
use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\AppConfig\SeesAutoQAButton;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPhones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function markAsPrimaryPhone(Request $request)
    {
        $phoneId       = $request->input('phoneId');
        $patientUserId = $request->input('patientUserId');

        if ( ! empty($phoneId) || ! empty($patientUserId)) {
            /** @var PhoneNumber $phones */
            $phones = $this->getAllNumbersOfPatient($patientUserId);
            foreach ($phones as $phone) {
                // One trip to DB
                $this->updatePreviousPrimaryPhone($phone, $phoneId);

                if ($phone->id === $phoneId) {
                    // One more trip to DB
                    $phone->is_primary = true;
                    $phone->save();
                }
            }

            return response()->json(
                [
                ],
                200
            );
        }

        //else
        return response()->json(
            [
                'message' => 'Required Parameters missing',
            ],
            400
        );
    }

    public function patientAjaxSearch(Request $request)
    {
        return view('wpUsers.patient.select');
    }

    /**
     * @return mixed
     */
    public static function phoneNumbersFor(Model $user)
    {
        return $user->phoneNumbers
            ->filter(function ($p) {
                return ! empty($p->number);
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
                $q->where('first_name', 'like', "%${term}%")
                    ->orWhere('last_name', 'like', "%${term}%")
                    ->orWhere('id', 'like', "%${term}%")
                    ->orWhereHas('patientInfo', function ($query) use ($term) {
                        $query->where('mrn_number', 'like', "%${term}%")
                            ->orWhere('birth_date', 'like', "%${term}%");
                    })
                    ->orWhereHas('phoneNumbers', function ($query) use ($term) {
                        $query->where('number', 'like', "%${term}%");
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
            $patients[$i]['link'] = route('patient.summary', ['patientId' => $d->id]);

            $programObj = Practice::find($d->program_id);

            $patients[$i]['program'] = $programObj->display_name ?? '';
            $patients[$i]['hint']    = $patients[$i]['name'].' DOB:'.$patients[$i]['dob'].' ['.$patients[$i]['program']."] MRN: {$patients[$i]['mrn']} ID: {$d->id} PRIMARY PHONE: {$d->getPrimaryPhone()}";
            ++$i;
        }

        return response()->json($patients);
    }

    public function saveNewPhoneNumber(Request $request)
    {
        $phoneType   = $request->input('phoneType');
        $phoneNumber = ($request->input('phoneNumber'));
        $userId      = $request->input('patientUserId');

        if ( ! ImportPhones::validatePhoneNumber($phoneNumber)) {
            return response()->json([
                'message' => 'Phone number is not a valid US number',
            ]);
        }

        $phoneNumber = formatPhoneNumberE164($phoneNumber);
        /** @var User $patientUser */
        $patientUser  = User::with('patientInfo', 'phoneNumbers')->where('id', $userId)->firstOrFail();
        $phoneNumbers = $this->getAllNumbersOfPatient($patientUser->id);
        $locationId   = optional($patientUser->patientInfo)->location->id ?? null;
        $numberExists = $patientUser->phoneNumbers()->where('type', $phoneType)->where('number', $phoneNumber)->exists();

        if ($numberExists) {
            return response()->json(
                [
                    'message' => "Phone Number with type '$phoneType' already exists for patient $userId",
                ],
            );
        }

        // One trip to DB
        $newPhoneNumber = PhoneNumber::firstOrCreate(
            [
                'user_id' => $userId,
                'number'  => $phoneNumber,
                'type'    => $phoneType,
            ],
            [
                'is_primary'  => $request->input('makePrimary'),
                'location_id' => $locationId,
            ]
        );

        foreach ($phoneNumbers->all() as $number) {
            // One more trip to DB
            $this->updatePreviousPrimaryPhone($number, $newPhoneNumber->id);
        }

        return response()->json([
            'data'    => $phoneNumber,
            'message' => 'Phone number has been saved!',
        ], 200);
    }

    public function showCallPatientPage(CallPatientRequest $request, $patientId)
    {
        $user = User::with('phoneNumbers')
            ->with('patientInfo.location')
            ->with('primaryPractice.locations')
            ->where('id', $patientId)
            ->firstOrFail();

        $phoneNumbers = self::phoneNumbersFor($user)->transform(function ($p) {
            return [
                'number' => $p->number,
                'type'   => ucfirst($p->type),
            ];
        });

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

        $phoneTypes = getPhoneTypes();
        //@todo:get phoneTypes and numbers from vue
        return view('wpUsers.patient.calls.index')
            ->with([
                'patient' => $user,
                //                'phoneNumbers'             => $phoneNumbers,
                'clinicalEscalationNumber' => $clinicalEscalationNumber,
                'cpmToken'                 => $cpmToken,
                //                'phoneTypes'               => $phoneTypes,
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
     * @return Response
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
        if ($isProvider && (User::SCOPE_LOCATION === auth()->user()->scope || 'false' === $showPracticePatientsInput)) {
            $showPracticePatients = false;
        }

        $storageDirectory = 'storage/pdfs/patients/';
        $datetimePrefix   = date('Y-m-dH:i:s');
        $fileName         = $storageDirectory.$datetimePrefix.'-patient-list.pdf';
        $file             = $pdfService->createPdfFromView('wpUsers.patient.listing-pdf', [
            'patients' => $this->formatter->patients(null, $showPracticePatients),
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
     * @param int $patientId
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
                    continue 1;
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
                $observation['description']         = $observation['obs_key'];
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
     * @param int $patientId
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

    /**
     * @return \App\PhoneNumber[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getAllNumbersOfPatient(int $patientUserId)
    {
        return PhoneNumber::whereUserId($patientUserId)->get();
    }

    private function updatePreviousPrimaryPhone(PhoneNumber $phone, int $newPhoneId)
    {
        if ($phone->id !== $newPhoneId && $phone->is_primary) {
            $phone->is_primary = false;
            $phone->save();
        }
    }
}
