<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient;

use App\CarePlan;
use App\CLH\Repositories\UserRepository;
use App\Contracts\ReportFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNewPatientRequest;
use App\Models\CCD\CcdInsurancePolicy;
use App\Patient;
use App\PatientContactWindow;
use App\Practice;
use App\Repositories\PatientReadRepository;
use App\Role;
use App\Services\CareplanService;
use App\Services\PatientService;
use App\Services\PdfService;
use App\User;
use Auth;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class PatientCareplanController extends Controller
{
    private $formatter;
    private $patientReadRepository;
    private $pdfService;

    public function __construct(
        ReportFormatter $formatter,
        PatientReadRepository $patientReadRepository,
        PdfService $pdfService
    ) {
        $this->formatter             = $formatter;
        $this->patientReadRepository = $patientReadRepository;
        $this->pdfService            = $pdfService;
    }

    public function createPatientDemographics(Request $request)
    {
        return $this->editOrCreateDemographics($request);
    }

    //Show Patient Careplan Print List  (URL: /manage-patients/careplan-print-list)
    public function index(Request $request)
    {
        $carePlansForWebix = collect();

        CarePlan::with('providerApproverUser')
            ->whereHas(
                    'patient',
                    function ($q) {
                        $q->intersectPracticesWith(auth()->user());
                    }
                )
            ->with(
                    [
                        'patient' => function ($q) {
                            $q
                                ->with(
                                    [
                                        'primaryPractice',
                                        'patientInfo',
                                    ]
                                )
                                ->intersectPracticesWith(auth()->user())
                                ->withCareTeamOfType('billing_provider');
                        },
                    ]
                )
            ->whereHas(
                    'patient.patientInfo',
                    function ($q) {
                        $q->enrolled();
                    }
                )
            ->chunk(
                    100,
                    function ($carePlans) use (&$carePlansForWebix) {
                        foreach ($carePlans as $cp) {
                            $last_printed = $cp->last_printed;

                            if ($last_printed) {
                                $printed_status = 'Yes';
                                $printed_date = $last_printed;
                            } else {
                                $printed_status = 'No';
                                $printed_date = null;
                            }
                            $last_printed
                                ? $printed = $last_printed
                                : $printed = 'No';

                            // careplan status stuff from 2.x
                            $careplanStatus = $cp->status;
                            $careplanStatusLink = '';
                            $approverName = 'NA';

                            if ('provider_approved' == $careplanStatus) {
                                $careplanStatus = $careplanStatusLink = 'Approved';

                                $approver = $cp->provider_approver_name;
                                if ($approver) {
                                    $approverName = $approver;
                                    $carePlanProviderDate = $cp->provider_date;

                                    $careplanStatusLink = '<span data-toggle="" title="'.$approverName.' '.$carePlanProviderDate.'">Approved</span>';
                                }
                            } elseif ('qa_approved' == $careplanStatus) {
                                $careplanStatus = 'Prov. to Approve';
                                $careplanStatusLink = 'Prov. to Approve';
                            } elseif ('draft' == $careplanStatus) {
                                $careplanStatus = 'CLH to Approve';
                                $careplanStatusLink = 'CLH to Approve';
                            }

                            if ($cp->patient->patientInfo
                                && ! empty($cp->patient->getFullName())
                                && ! empty($cp->patient->first_name)
                                && ! empty($cp->patient->last_name)) {
                                $carePlansForWebix->push(
                                    [
                                        'key'                        => $cp->patient->id,
                                        'id'                         => $cp->patient->id,
                                        'patient_name'               => $cp->patient->getFullName(),
                                        'first_name'                 => $cp->patient->getFirstName(),
                                        'last_name'                  => $cp->patient->getLastName(),
                                        'careplan_status'            => $careplanStatus,
                                        'careplan_status_link'       => $careplanStatusLink,
                                        'careplan_provider_approver' => $approverName,
                                        'dob'                        => Carbon::parse(
                                            $cp->patient->getBirthDate()
                                        )->format('m/d/Y'),
                                        'phone'    => '',
                                        'age'      => $cp->patient->getAge(),
                                        'reg_date' => Carbon::parse(
                                            $cp->patient->getRegistrationDate()
                                        )->format('m/d/Y'),
                                        'last_read'             => '',
                                        'ccm_time'              => $cp->patient->getCcmTime(),
                                        'ccm_seconds'           => $cp->patient->getCcmTime(),
                                        'provider'              => $cp->patient->getBillingProviderName(),
                                        'program_name'          => $cp->patient->getPrimaryPracticeName(),
                                        'careplan_last_printed' => $printed_date,
                                        'careplan_printed'      => $printed_status,
                                    ]
                                );
                            }
                        }
                    }
                );

        $patientJson = $carePlansForWebix->toJson();

        return view(
            'wpUsers.patient.careplan.printlist',
            compact(
                ['patientJson']
            )
        );
    }

    public function printMultiCareplan(
        Request $request,
        CareplanService $careplanService,
        PatientService $patientService
    ) {
        if ( ! $request['users']) {
            return response()->json('Something went wrong..');
        }

        //Welcome Letter Check
        $letter = false;

        if (isset($request['letter'])) {
            $letter = true;
        }

        $users = explode(',', $request['users']);

        if ($request->input('final')) {
            foreach ($users as $userId) {
                $careplanService->repo()->approve($userId, auth()->user()->id);
                $patientService->setStatus($userId, Patient::ENROLLED);
            }
        }

        $storageDirectory = 'storage/pdfs/careplans/';
        $pageFileNames    = [];

        $fileNameWithPathBlankPage = $this->pdfService->blankPage();

        // create pdf for each user
        $p = 1;
        foreach ($users as $user_id) {
            $user = User::with(['careTeamMembers', 'carePlan.pdfs'])->find($user_id);

            $careplan = $this->formatter->formatDataForViewPrintCareplanReport([$user]);
            $careplan = $careplan[$user_id];
            if (empty($careplan)) {
                return false;
            }

            $pageCount = 0;

            if ($request->filled('render') && 'html' == $request->input('render')) {
                return view(
                    'wpUsers.patient.multiview',
                    [
                        'careplans'    => [$user_id => $careplan],
                        'isPdf'        => true,
                        'letter'       => $letter,
                        'problemNames' => $careplan['problem'],
                        'careTeam'     => $user->careTeamMembers,
                        'data'         => $careplanService->careplan($user_id),
                    ]
                );
            }

            $pdfCareplan = null;
            if (true == $letter && 'pdf' == $user->carePlan->mode) {
                $pdfCareplan = $user->carePlan->pdfs->sortByDesc('created_at')->first();
            }

            $fileNameWithPath = $this->pdfService->createPdfFromView(
                'wpUsers.patient.multiview',
                [
                    'careplans'    => [$user_id => $careplan],
                    'isPdf'        => true,
                    'letter'       => $letter,
                    'problemNames' => $careplan['problem'],
                    'careTeam'     => $user->careTeamMembers,
                    'data'         => $careplanService->careplan($user_id),
                    'pdfCareplan'  => $pdfCareplan,
                ],
                [
                    'disable-javascript' => true,
                ]
            );

            $pageCount = $this->pdfService->countPages($fileNameWithPath);
            // append blank page if needed
            if ((count($users) > 1) && 0 != $pageCount % 2) {
                $fileNameWithPath = $this->pdfService->mergeFiles(
                    [
                        $fileNameWithPath,
                        $fileNameWithPathBlankPage,
                    ],
                    $fileNameWithPath
                );
            }

            // add to array
            $pageFileNames[] = $fileNameWithPath;

            if (auth()->user()->isAdmin() && true == $letter) {
                $careplanObj               = $user->carePlan;
                $careplanObj->last_printed = Carbon::now()->toDateTimeString();
                if ( ! $careplanObj->first_printed) {
                    $careplanObj->first_printed    = Carbon::now()->toDateTimeString();
                    $careplanObj->first_printed_by = auth()->id();
                }
                $careplanObj->save();
            }

            ++$p;
        }

        // merge to final file
        $mergedFileNameWithPath = $this->pdfService->mergeFiles($pageFileNames);

        return response()->file($mergedFileNameWithPath);
    }

    public function showPatientDemographics(
        Request $request,
        $patientId
    ) {
        return $this->editOrCreateDemographics($request, $patientId);
    }

    public function storePatientDemographics(
        CreateNewPatientRequest $request
    ) {
        return $this->storeOrUpdateDemographics($request);
    }

    /**
     * Change CarePlan Mode to Pdf.
     *
     * @param $carePlanId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchToPdfMode(
        $carePlanId
    ) {
        $cp = CarePlan::find($carePlanId);

        $cp->mode = CarePlan::PDF;
        $cp->save();

        return redirect()->route('patient.pdf.careplan.print', ['patientId' => $cp->user_id]);
    }

    /**
     * Change CarePlan Mode to Web.
     *
     * @param $carePlanId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchToWebMode(
        $carePlanId
    ) {
        $cp = CarePlan::find($carePlanId);

        $cp->mode = CarePlan::WEB;
        $cp->save();

        return redirect()->route('patient.careplan.print', ['patientId' => $cp->user_id]);
    }

    public function updatePatientDemographics(
        Request $request
    ) {
        return $this->storeOrUpdateDemographics($request);
    }

    private function editOrCreateDemographics(
        Request $request,
        $patientId = null
    ) {
        $messages = \Session::get('messages');

        // determine if existing user or new user
        $user      = new User();
        $programId = false;
        if ($patientId) {
            $user = User::with('patientInfo.contactWindows')->find($patientId);
            if ( ! $user) {
                return response('User not found', 401);
            }
            $programId = $user->program_id;
        }
        $patient = $user;

        // locations @todo get location id for Program
        $program   = Practice::find($programId);
        $locations = [];
        if ($program) {
            $locations = $program->locations->pluck('name', 'id')->all();
        }

        // get program
        $programs = Practice::whereIn('id', Auth::user()->viewableProgramIds())->pluck(
            'display_name',
            'id'
        )->all();

        $billingProviders = User::ofType('provider')->ofPractice(Auth::user()->program_id)->pluck(
            'display_name',
            'id'
        )->all();

        // roles
        $patientRoleId = Role::where('name', '=', 'participant')->first();
        $patientRoleId = $patientRoleId->id;

        // States (for dropdown)
        $states = [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District Of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];

        // timezones for dd
        $timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($timezones_raw as $timezone) {
            $timezones[$timezone] = $timezone;
        }

        $showApprovalButton = false; // default hide
        if (Auth::user()->hasRole(['provider'])) {
            if ('provider_approved' != $patient->getCarePlanStatus()) {
                $showApprovalButton = true;
            }
        } else {
            if ('draft' == $patient->getCarePlanStatus()) {
                $showApprovalButton = true;
            }
        }

        $insurancePolicies = $patient->ccdInsurancePolicies()->get();

        $contact_days_array = [];
        if ($patient->patientInfo()->exists()) {
            $contactWindows     = $patient->patientInfo->contactWindows;
            $contact_days_array = $contactWindows->pluck('day_of_week')->toArray();
        }

        return view(
            'wpUsers.patient.careplan.patient',
            compact(
                [
                    'patient',
                    'userMeta',
                    'userConfig',
                    'states',
                    'locations',
                    'timezones',
                    'messages',
                    'patientRoleId',
                    'programs',
                    'programId',
                    'showApprovalButton',
                    'insurancePolicies',
                    'contact_days_array',
                    'contactWindows',
                    'billingProviders',
                ]
            )
        );
    }

    private function storeOrUpdateDemographics(
        Request $request
    ) {
        $params    = new ParameterBag($request->input());
        $patientId = false;
        if ($params->get('user_id')) {
            $patientId = $params->get('user_id');
        }

        // instantiate user
        $user = new User();
        if ($patientId) {
            $user = User::with('phoneNumbers', 'patientInfo', 'careTeamMembers')->find($patientId);
            if ( ! $user) {
                return response('User not found', 401);
            }
        }

        if ($params->has('insurance')) {
            foreach ($params->get('insurance') as $id => $approved) {
                if ( ! $approved) {
                    CcdInsurancePolicy::destroy($id);
                    continue;
                }

                $insurance           = CcdInsurancePolicy::find($id);
                $insurance->approved = true;
                $insurance->save();
            }
        }

        $userRepo = new UserRepository();

        if ($patientId) {
            $patient = User::where('id', $patientId)->first();
            //Update patient info changes
            $info = $patient->patientInfo;

            if ( ! $patient->patientInfo) {
                $info = new Patient(
                    [
                        'user_id' => $patient->id,
                    ]
                );
            }

            if ($params->get('general_comment')) {
                $info->general_comment = $params->get('general_comment');
            }
            if ($params->get('frequency')) {
                $info->preferred_calls_per_month = $params->get('frequency');
            }
            //we are checking this $info->contactWindows()->exists()
            //in case we want to delete all call windows, since $params->get('days') will evaluate to null if we unselect all
            if ($params->get('days') || $info->contactWindows()->exists()) {
                PatientContactWindow::sync(
                    $info,
                    $params->get('days', []),
                    $params->get('window_start'),
                    $params->get('window_end')
                );
            }
            $info->save();
            // validate
            $messages = [
                'required'                   => 'The :attribute field is required.',
                'home_phone_number.required' => 'The patient phone number field is required.',
            ];
            $this->validate($request, $user->patient_rules, $messages);
            $userRepo->editUser($user, $params);
            if ($params->get('direction')) {
                return redirect($params->get('direction'))->with(
                    'messages',
                    ['Successfully updated patient demographics.']
                );
            }

            return redirect()->back()->with('messages', ['Successfully updated patient demographics.']);
        }
        // validate
        $messages = [
            'required'                   => 'The :attribute field is required.',
            'home_phone_number.required' => 'The patient phone number field is required.',
        ];
        $this->validate($request, $user->patient_rules, $messages);
        $role      = Role::whereName('participant')->first();
        $newUserId = str_random(15);
        $params->add(
            [
                'username' => $newUserId,
                'email'    => empty($email = $params->get('email'))
                    ? $newUserId.'@careplanmanager.com'
                    : $email,
                'password'        => $newUserId,
                'user_status'     => '1',
                'program_id'      => $params->get('program_id'),
                'display_name'    => $params->get('first_name').' '.$params->get('last_name'),
                'roles'           => [$role->id],
                'ccm_status'      => $request->input('ccm_status', Patient::ENROLLED),
                'careplan_status' => 'draft',
                'careplan_mode'   => CarePlan::WEB,
            ]
        );
        $newUser = $userRepo->createNewUser($user, $params);

        if ($request->has('provider_id')) {
            $newUser->setBillingProviderId($request->input('provider_id'));
        }

        if ($newUser) {
            //Update patient info changes
            $info = $newUser->patientInfo;
            //in case we want to delete all call windows
            if ($params->get('days') || $info->contactWindows()->exists()) {
                PatientContactWindow::sync(
                    $info,
                    $params->get('days', []),
                    $params->get('window_start'),
                    $params->get('window_end')
                );
            }
            $info->save();

            if ($newUser->carePlan && ! $newUser->primaryPractice->settings->isEmpty()) {
                $newUser->carePlan->mode = $newUser->primaryPractice->settings->first()->careplan_mode;
                $newUser->carePlan->save();
            }
        }

        return redirect(\route('patient.demographics.show', ['patientId' => $newUser->id]))->with(
            'messages',
            ['Successfully created new patient with demographics.']
        );
    }
}
