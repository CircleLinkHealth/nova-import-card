<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\CarePlanPrintListView;
use App\Constants;
use App\Contracts\ReportFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNewPatientRequest;
use App\Relationships\PatientCareplanRelations;
use App\Repositories\PatientReadRepository;
use App\Services\CareplanService;
use App\Services\PatientService;
use Auth;
use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Exceptions\PatientAlreadyExistsException;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public function deletePhoneNumber(Request $request)
    {
//        @todo: Check user id in form request.
        if ( ! $request->input('deleteAltPhone')) {
            if (empty($request->input('phoneId'))) {
                return response()->json([
                    'message' => 'Phone id is null',
                ], 400);
            }

            $phoneNumber = PhoneNumber::whereId($request->input('phoneId'));
            if ( ! empty($phoneNumber->first()) && $phoneNumber->first()->is_primary) {
                return response()->json([
                    'message' => 'You cannot delete a primary number',
                ]);
            }

            $phoneNumber->delete();
        } else {
            Patient::whereUserId($request->input('patientUserId'))->update(
                [
                    'agent_telephone' => null,
                ]
            );
        }

        return response()->json([
            'message' => 'Phone Number Has Been Deleted',
        ], 200);
    }

    public static function getPatientPhoneNumbers(Request $request)
    {
        if (empty($request->input('userId'))) {
            return response()->json([
                'message' => 'User id is null',
            ], 400);
        }

        /** @var User $patient */
        $patient = User::with('phoneNumbers', 'patientInfo')
            ->where('id', '=', $request->input('userId'))
            ->first();

        if (empty($patient)) {
            return response()->json([
                'message' => "User [$patient->id] does not exist.",
            ], 400);
        }

        $phoneNumbers = PatientController::phoneNumbersFor($patient)->transform(function ($phone) {
            return [
                'phoneNumberId' => $phone->id,
                'number'        => substr(formatPhoneNumberE164($phone->number), 2),
                'type'          => ucfirst($phone->type),
                'isPrimary'     => boolval($phone->is_primary),
            ];
        });

        $agentContactFields = $patient
            ->patientInfo()
            ->select('agent_email', 'agent_relationship', 'agent_telephone', 'agent_name')
            ->get()
            ->transform(function ($patient) {
                return [
                    'agentEmail'        => $patient->agent_email,
                    'agentRelationship' => $patient->agent_relationship,
                    'agentTelephone'    => [
                        'isPrimary' => false,
                        'number'    => $patient->agent_telephone,
                        'type'      => ucwords(Patient::AGENT),
                    ],

                    'agentName' => $patient->agent_name,
                ];
            });

        if ( ! empty($agentContactFields->first()) && ! is_null($agentContactFields->first()['agentTelephone'])) {
            $phoneNumbers = collect($phoneNumbers)->merge([$agentContactFields->first()['agentTelephone']]);
        }

        $phoneTypes = getPhoneTypes();

        return response()->json([
            'phoneNumbers'       => $phoneNumbers,
            'phoneTypes'         => $phoneTypes,
            'agentContactFields' => $agentContactFields,
        ], 200);
    }

    public function index()
    {
        $practiceIds       = auth()->user()->viewableProgramIds();
        $carePlansForWebix = collect();

        $query = CarePlanPrintListView::whereIn('primary_practice_id', $practiceIds)
            ->get()
            ->each(
                function ($cp) use (&$carePlansForWebix) {
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
                    $careplanStatus = $cp->care_plan_status;
                    $careplanStatusLink = '';
                    $approverName = 'NA';

                    if (CarePlan::PROVIDER_APPROVED == $careplanStatus) {
                        $careplanStatus = $careplanStatusLink = 'Approved';

                        $approver = $cp->approver_full_name;
                        if ($approver) {
                            $approverName = $approver;
                            $carePlanProviderDate = $cp->provider_date;

                            $careplanStatusLink = '<span data-toggle="" title="'.$approverName.' '.$carePlanProviderDate.'">Approved</span>';
                        }
                    } elseif (CarePlan::RN_APPROVED == $careplanStatus) {
                        $careplanStatus = 'Prov. to Approve';
                        $careplanStatusLink = 'Prov. to Approve';
                    } elseif (CarePlan::QA_APPROVED == $careplanStatus) {
                        $careplanStatus = 'RN to Approve';
                        $careplanStatusLink = 'RN to Approve';
                    } elseif (CarePlan::DRAFT == $careplanStatus) {
                        $careplanStatus = 'CLH to Approve';
                        $careplanStatusLink = 'CLH to Approve';
                    }

                    $from = new DateTime($cp->patient_dob);
                    $to = new DateTime('today');

                    $age = $from->diff($to)->y;

                    if ( ! empty($cp->patient_info_id) && ! empty($cp->patient_first_name)
                        && ! empty($cp->patient_last_name)) {
                        $carePlansForWebix->push(
                            [
                                'id'                         => $cp->patient_id,
                                'key'                        => $cp->patient_id,
                                'patient_name'               => $cp->patient_full_name,
                                'first_name'                 => $cp->patient_first_name,
                                'last_name'                  => $cp->patient_last_name,
                                'careplan_status'            => $careplanStatus,
                                'careplan_status_link'       => $careplanStatusLink,
                                'careplan_provider_approver' => $approverName,
                                'dob'                        => Carbon::parse(
                                    $cp->patient_dob
                                )->format('m/d/Y'),
                                'phone'    => '',
                                'age'      => $age,
                                'reg_date' => Carbon::parse(
                                    $cp->patient_registered
                                )->format('m/d/Y'),
                                'last_read'             => '',
                                'ccm_time'              => $cp->patient_ccm_time,
                                'ccm_seconds'           => $cp->patient_ccm_time,
                                'provider'              => $cp->provider_full_name,
                                'program_name'          => $cp->practice_name,
                                'careplan_last_printed' => $printed_date,
                                'careplan_printed'      => $printed_status,
                            ]
                        );
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

        $userIds = explode(',', $request['users']);

        if ($request->input('final')) {
            foreach ($userIds as $userId) {
                $careplanService->repo()->approve($userId, auth()->user()->id);
                $patientService->setStatus($userId, Patient::ENROLLED);
            }
        }

        $storageDirectory = 'storage/pdfs/careplans/';
        $pageFileNames    = [];

        $fileNameWithPathBlankPage = $this->pdfService->blankPage();

        $users = User::with(
            array_merge(PatientCareplanRelations::get(), [
                'patientInfo',
                'primaryPractice',
                'inboundCalls' => function ($c) {
                    $c->with(['outboundUser'])
                        ->where('status', 'scheduled')
                        ->where('called_date', '=', null);
                }, ])
        )
            ->has('patientInfo')
            ->has('billingProvider.user')
            ->findMany($userIds);

        // create pdf for each user
        $p = 1;
        foreach ($users as $user) {
            $careplan = $this->formatter->formatDataForViewPrintCareplanReport($user);
            $careplan = $careplan[$user->id];
            if (empty($careplan)) {
                return false;
            }

            $pageCount         = 0;
            $gender            = $user->patientInfo->gender;
            $title             = 'm' === strtolower($gender) ? 'Mr.' : ('f' === strtolower($gender) ? 'Ms.' : null);
            $practiceNumber    = $user->primaryPractice->number_with_dashes;
            $assignedNurseName = optional(app(NurseFinderEloquentRepository::class)->find($user->id))->first_name;

            //if permanent assigned nurse does not exist, get nurse from scheduled call - CPM-1829
            if ( ! $assignedNurseName) {
                $call              = $user->inboundCalls->first();
                $assignedNurseName = $call ? optional($call->outboundUser)->first_name : null;
            }

            $viewParams = [
                'careplans'         => [$user->id => $careplan],
                'isPdf'             => true,
                'letter'            => $letter,
                'problemNames'      => $careplan['problem'],
                'patient'           => $user,
                'careTeam'          => $user->careTeamMembers,
                'data'              => $careplanService->careplan($user->id),
                'billingDoctor'     => $user->billingProviderUser(),
                'regularDoctor'     => $user->regularDoctorUser(),
                'title'             => $title,
                'practiceNumber'    => $practiceNumber,
                'assignedNurseName' => $assignedNurseName,
            ];

            if ($request->filled('render') && 'html' == $request->input('render')) {
                return view('wpUsers.patient.multiview', $viewParams);
            }

            $pdfCareplan = null;
            if (true == $letter && 'pdf' == $user->carePlan->mode) {
                $viewParams['pdfCarePlan'] = $user->carePlan->pdfs->sortByDesc('created_at')->first();
            }

            $fileNameWithPath = $this->pdfService->createPdfFromView(
                'wpUsers.patient.multiview',
                $viewParams,
                null,
                Constants::SNAPPY_CLH_MAIL_VENDOR_SETTINGS
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
        $programId = null;
        $program   = null;
        $locations = [];
        $providers = [];
        if ($patientId) {
            $user = User::with(['patientInfo.contactWindows', 'careTeamMembers', 'ccdInsurancePolicies', 'carePlan', 'primaryPractice.locations' => function ($q) {
                $q->when(User::SCOPE_LOCATION === auth()->user()->scope, function ($q) {
                    $q->whereIn('id', auth()->user()->viewableLocationIds());
                })->whereHas('providers')->with('providers');
            }])->find($patientId);
            if ( ! $user) {
                return response('User not found', 401);
            }
            $programId = $user->program_id;
            $program   = $user->primaryPractice;
            $locations = $program->locations->pluck('name', 'id')->all();
            if ($user->patientInfo) {
                $patientLocation = $program->locations->where('id', $user->patientInfo->preferred_contact_location)->first();
                if ($patientLocation && $patientLocation->providers) {
                    $providers = $patientLocation->providers->pluck('display_name', 'id')->all();
                }
            }
        }
        $patient = $user;

        $programs = Practice::whereHas('locations.providers')
            ->whereIn('id', Auth::user()->viewableProgramIds())
            ->pluck('display_name', 'id')
            ->all();

        $programId ??= array_keys($programs)[0] ?? null;

        $patientRoleId = Role::byName('participant')->id;

        $reasons = [
            'No Longer Interested',
            'Moving out of Area',
            'New Physician',
            'Cost / Co-Pay',
            'Changed Insurance',
            'Dialysis / End-Stage Renal Disease',
            'Expired',
            'Patient in Hospice',
            'Other',
        ];

        $withdrawnReasons       = array_combine($reasons, $reasons);
        $patientWithdrawnReason = $patient->getWithdrawnReason();

        $states = usStatesArrayForDropdown();

        $timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($timezones_raw as $timezone) {
            $timezones[$timezone] = $timezone;
        }

        $showApprovalButton = false;
        if (Auth::user()->isProvider()) {
            if (CarePlan::PROVIDER_APPROVED != $patient->getCarePlanStatus()) {
                $showApprovalButton = true;
            }
        } else {
            if (CarePlan::DRAFT == $patient->getCarePlanStatus()) {
                $showApprovalButton = true;
            }
        }

        $insurancePolicies = $patient->ccdInsurancePolicies;

        $contact_days_array = [];
        $contactWindows     = [];
        if ($patient->patientInfo) {
            $contactWindows     = $patient->patientInfo->contactWindows;
            $contact_days_array = $contactWindows->pluck('day_of_week')->toArray();
        }

        $billingProviderUserId = $patient->getBillingProviderId();

        $phoneNumbers = PatientController::phoneNumbersFor($patient)->transform(function ($phone) {
            return [
                'phoneNumberId' => $phone->id,
                'number'        => substr(formatPhoneNumberE164($phone->number), 2),
                'type'          => ucfirst($phone->type),
                'inputDisabled' => true,
            ];
        });

        $phoneTypes = getPhoneTypes();

        return view(
            'wpUsers.patient.careplan.patient',
            compact(
                [
                    'phoneNumbers',
                    'providers',
                    'locations',
                    'billingProviderUserId',
                    'phoneTypes',
                    'patient',
                    'states',
                    'timezones',
                    'messages',
                    'patientRoleId',
                    'programs',
                    'programId',
                    'showApprovalButton',
                    'insurancePolicies',
                    'contact_days_array',
                    'contactWindows',
                    'withdrawnReasons',
                    'patientWithdrawnReason',
                ]
            )
        );
    }

    //Show Patient Careplan Print List  (URL: /manage-patients/careplan-print-list)

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

        //moving here to cover all cases
        if (in_array($params->get('ccm_status'), [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL])) {
            if ('Other' == $params->get('withdrawn_reason')) {
                $params->set('withdrawn_reason', $params->get('withdrawn_reason_other'));
            }
        } else {
            $params->set('withdrawn_reason', null);
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

            if ($user->first_name) {
                $params->set('first_name', $user->first_name);
            }
            if ($user->last_name) {
                $params->set('last_name', $user->last_name);
            }

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

            $info->withdrawn_reason = $params->get('withdrawn_reason');

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

            $v = Validator::make($params->all(), $user->getPatientRules(), $messages);
            if ($v->fails()) {
                return redirect()->back()->withErrors($v->errors())->withInput($request->input());
            }
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
        $v = Validator::make($params->all(), $user->getPatientRules(), $messages);
        if ($v->fails()) {
            return redirect()->back()->withErrors($v->errors())->withInput($request->input());
        }
        $role      = Role::byName('participant');
        $newUserId = Str::random(15);

        $carePlanStatus = CarePlan::DRAFT;
        if (auth()->user()->isPracticeStaff()) {
            $carePlanStatus = CarePlan::QA_APPROVED;
        }

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
                'careplan_status' => $carePlanStatus,
                'careplan_mode'   => CarePlan::WEB,
            ]
        );
        try {
            $newUser = $userRepo->createNewUser($params);
        } catch (PatientAlreadyExistsException $e) {
            return redirect()
                ->back()
                ->withErrors(['first_name' => $e->getMessage()])
                ->withInput($request->input());
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->validator->errors())
                ->withInput($request->input());
        }

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
