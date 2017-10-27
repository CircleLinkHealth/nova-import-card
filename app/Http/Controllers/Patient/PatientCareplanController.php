<?php namespace App\Http\Controllers\Patient;

use App\CarePlan;
use App\CLH\Repositories\UserRepository;
use App\Events\CarePlanWasApproved;
use App\Formatters\WebixFormatter;
use App\Http\Controllers\Controller;
use App\Models\CCD\CcdInsurancePolicy;
use App\Models\CPM\Biometrics\CpmBloodPressure;
use App\Models\CPM\Biometrics\CpmBloodSugar;
use App\Models\CPM\Biometrics\CpmSmoking;
use App\Models\CPM\Biometrics\CpmWeight;
use App\Patient;
use App\PatientContactWindow;
use App\Practice;
use App\Role;
use App\Services\CarePlanViewService;
use App\Services\CPM\CpmBiometricService;
use App\Services\CPM\CpmLifestyleService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Services\CPM\CpmMiscService;
use App\Services\CPM\CpmProblemService;
use App\Services\CPM\CpmSymptomService;
use App\Services\ReportsService;
use App\Services\UserService;
use App\User;
use Auth;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class PatientCareplanController extends Controller
{
    //Show Patient Careplan Print List  (URL: /manage-patients/careplan-print-list)
    public function index(Request $request)
    {
        $patientData = [];

        $patients = User::intersectPracticesWith(auth()->user())
            ->ofType('participant')
            ->with('primaryPractice')
            ->with([
                'carePlan' => function ($q) {
                    $q->with('providerApproverUser');
                },
            ])
            ->withCareTeamOfType('billing_provider')
            ->get();

        foreach ($patients as $patient) {

            $last_printed = $patient->careplan_last_printed;

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
            $careplanStatus = $patient->carePlanStatus;
            $careplanStatusLink = '';
            $approverName = 'NA';
            $tooltip = 'NA';

            if ($careplanStatus == 'provider_approved') {
                $careplanStatus = $careplanStatusLink = 'Approved';

                $approver = $patient->carePlan->providerApproverUser;
                if ($approver) {
                    $approverName = $approver->fullName;
                    $carePlanProviderDate = $patient->carePlanProviderDate;

                    $careplanStatusLink = '<span data-toggle="" title="' . $approverName . ' ' . $carePlanProviderDate . '">Approved</span>';
                    $tooltip = $approverName . ' ' . $carePlanProviderDate;
                }
            } else {
                if ($careplanStatus == 'qa_approved') {
                    $careplanStatus = 'Prov. to Approve';
                    $tooltip = $careplanStatus;
                    $careplanStatusLink = 'Prov. to Approve';
                } else {
                    if ($careplanStatus == 'draft') {
                        $careplanStatus = 'CLH to Approve';
                        $tooltip = $careplanStatus;
                        $careplanStatusLink = 'CLH to Approve';
                    }
                }
            }

            if ($patient->patientInfo) {
                $patientData[] = [
                    'key'                        => $patient->id,
                    'id'                         => $patient->id,
                    'patient_name'               => $patient->fullName,
                    'first_name'                 => $patient->first_name,
                    'last_name'                  => $patient->last_name,
                    'careplan_status'            => $careplanStatus,
                    'careplan_status_link'       => $careplanStatusLink,
                    'careplan_provider_approver' => $approverName,
                    'dob'                        => Carbon::parse($patient->birthDate)->format('m/d/Y'),
                    'phone'                      => '',
                    'age'                        => $patient->age,
                    'reg_date'                   => Carbon::parse($patient->registrationDate)->format('m/d/Y'),
                    'last_read'                  => '',
                    'ccm_time'                   => $patient->patientInfo->cur_month_activity_time,
                    'ccm_seconds'                => $patient->patientInfo->cur_month_activity_time,
                    'provider'                   => $patient->billingProviderName,
                    'program_name'               => $patient->primaryPracticeName,
                    'careplan_last_printed'      => $printed_date,
                    'careplan_printed'           => $printed_status,
                ];
            }
        }

        $patientJson = json_encode($patientData);

        return view('wpUsers.patient.careplan.printlist', compact([
            'patientJson',
        ]));
    }

    public function printMultiCareplan(Request $request)
    {
        if (!$request['users']) {
            return response()->json("Something went wrong..");
        }

        //Welcome Letter Check
        $letter = false;

        if (isset($request['letter'])) {
            $letter = true;
        }

        $users = explode(',', $request['users']);
        $reportService = new ReportsService($users);
        //Save Printed Careplan as Meta
        foreach ($users as $user_id) {
            $user = User::find($user_id);
            if ($user) {
                $user->careplan_last_printed = Carbon::now();
                $user->save();
            }
        }

        // vars
        $storageDirectory = 'storage/pdfs/careplans/';
        $datetimePrefix = date('Y-m-dH:i:s');
        $pageFileNames = [];

        try {
            // first create blank page
            $pdf = App::make('snappy.pdf.wrapper');
            $pdf->loadView('wpUsers.patient.careplan.print', [
                'patient' => false,
                'isPdf'   => true,
            ]);

            $fileNameBlankPage = $storageDirectory . $datetimePrefix . '-0-PDFblank.pdf';
            $fileNameWithPathBlankPage = base_path($fileNameBlankPage);
            $pdf->save($fileNameWithPathBlankPage, true);
        } catch (\Exception $e) {
            \Log::critical($e);
        }

        // create pdf for each user
        $p = 1;
        foreach ($users as $user_id) {
            // add p to datetime prefix
            $prefix = $datetimePrefix . '-' . $p;
            $user = User::find($user_id);
            $formatter = new WebixFormatter();
            $careplan = $formatter->formatDataForViewPrintCareplanReport([$user]);
            $careplan = $careplan[$user_id];
            if (empty($careplan)) {
                return false;
            }
            try {
                //HTML render to help us with debugging
                if ($request->has('render') && $request->input('render') == 'html') {
                    return view('wpUsers.patient.multiview', [
                        'careplans'    => [$user_id => $careplan],
                        'isPdf'        => true,
                        'letter'       => $letter,
                        'problemNames' => $careplan['problem'],
                        'careTeam'     => $user->careTeamMembers,
                    ]);
                }

                // build pdf
                $pdf = App::make('snappy.pdf.wrapper');
//            leaving these here in case we need them
//            $pdf->setOption('disable-javascript', false);
//            $pdf->setOption('enable-javascript', true);
//            $pdf->setOption('javascript-delay', 400);

                $pdf->loadView('wpUsers.patient.multiview', [
                    'careplans'    => [$user_id => $careplan],
                    'isPdf'        => true,
                    'letter'       => $letter,
                    'problemNames' => $careplan['problem'],
                    'careTeam'     => $user->careTeamMembers,
                ]);
                $pdf->setOption('footer-center', 'Page [page]');
                $pdf->setOption('margin-top', '25');
                $pdf->setOption('margin-left', '5');
                $pdf->setOption('margin-bottom', '20');
                $pdf->setOption('margin-right', '31.75'); //1.25 inches

                $fileName = $storageDirectory . $prefix . '-PDF_' . str_random(40) . '.pdf';
                $fileNameWithPath = base_path($fileName);

                $pdf->save($fileNameWithPath, true);
            } catch (\Exception $e) {
                \Log::critical($e);
            }
            $pageCount = $this->count_pages($fileNameWithPath);
//            echo PHP_EOL . '<br /><br />' . $fileNameWithPath . ' - PAGE COUNT: ' . $pageCount;

            // append blank page if needed
            if ((count($users) > 1) && $pageCount % 2 != 0) {
                $fileName = $storageDirectory . $this->merge_pages([
                        $fileName,
                        $fileNameBlankPage,
                    ], $prefix, $storageDirectory);
                $fileNameWithPath = base_path($fileName);
            }

            // add to array
            $pageFileNames[] = $fileName;

            $p++;
        }

        // merge to final file
        $mergedFileName = $this->merge_pages($pageFileNames, $datetimePrefix, $storageDirectory);
        $mergedFileNameWithPath = $storageDirectory . $this->merge_pages($pageFileNames, $datetimePrefix,
                $storageDirectory);

        //dd($mergedFileName . ' - PAGE COUNT: '.$this->count_pages(base_path($mergedFileNameWithPath)));

        return Response::make(file_get_contents(base_path($mergedFileNameWithPath)), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $mergedFileName . '"',
        ]);
        //return view('wpUsers.patient.multiview', compact(['careplans']));
    }

    public function count_pages($pdfname)
    {
        $pdftext = file_get_contents($pdfname);
        $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);

        return $num;
    }

    public function merge_pages(
        $fileArray,
        $prefix = '',
        $storageDirectory = ''
    ) {
        //$fileArray= array("name1.pdf","name2.pdf","name3.pdf","name4.pdf");

        $outputFileName = $prefix . "-merged.pdf";
        $outputName = base_path($storageDirectory . $prefix . "-merged.pdf");

        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";
//Add each pdf file to the end of the command
        foreach ($fileArray as $file) {
            $cmd .= base_path($file) . " ";
        }
        $result = shell_exec($cmd);

        return $outputFileName;
    }

    /**
     * Display patient add/edit
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showPatientDemographics(
        Request $request,
        $patientId = false
    ) {
        $messages = \Session::get('messages');

        // determine if existing user or new user
        $user = new User;
        $programId = false;
        if ($patientId) {
            $user = User::with('patientInfo.patientContactWindows')->find($patientId);
            if (!$user) {
                return response("User not found", 401);
            }
            $programId = $user->program_id;
        }
        $patient = $user;

        // security
        if (!Auth::user()->can('observations-view')) {
            abort(403);
        }

        // get program
        $programs = Practice::whereIn('id', Auth::user()->viewableProgramIds())->pluck('display_name',
            'id')->all();

        // roles
        $patientRoleId = Role::where('name', '=', 'participant')->first();
        $patientRoleId = $patientRoleId->id;

        // locations @todo get location id for Program
        $program = Practice::find($programId);
        $locations = [];
        if ($program) {
            $locations = $program->locations->pluck('name', 'id')->all();
        }

        // States (for dropdown)
        $states = [
            'AL' => "Alabama",
            'AK' => "Alaska",
            'AZ' => "Arizona",
            'AR' => "Arkansas",
            'CA' => "California",
            'CO' => "Colorado",
            'CT' => "Connecticut",
            'DE' => "Delaware",
            'DC' => "District Of Columbia",
            'FL' => "Florida",
            'GA' => "Georgia",
            'HI' => "Hawaii",
            'ID' => "Idaho",
            'IL' => "Illinois",
            'IN' => "Indiana",
            'IA' => "Iowa",
            'KS' => "Kansas",
            'KY' => "Kentucky",
            'LA' => "Louisiana",
            'ME' => "Maine",
            'MD' => "Maryland",
            'MA' => "Massachusetts",
            'MI' => "Michigan",
            'MN' => "Minnesota",
            'MS' => "Mississippi",
            'MO' => "Missouri",
            'MT' => "Montana",
            'NE' => "Nebraska",
            'NV' => "Nevada",
            'NH' => "New Hampshire",
            'NJ' => "New Jersey",
            'NM' => "New Mexico",
            'NY' => "New York",
            'NC' => "North Carolina",
            'ND' => "North Dakota",
            'OH' => "Ohio",
            'OK' => "Oklahoma",
            'OR' => "Oregon",
            'PA' => "Pennsylvania",
            'RI' => "Rhode Island",
            'SC' => "South Carolina",
            'SD' => "South Dakota",
            'TN' => "Tennessee",
            'TX' => "Texas",
            'UT' => "Utah",
            'VT' => "Vermont",
            'VA' => "Virginia",
            'WA' => "Washington",
            'WV' => "West Virginia",
            'WI' => "Wisconsin",
            'WY' => "Wyoming",
        ];

        // timezones for dd
        $timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($timezones_raw as $timezone) {
            $timezones[$timezone] = $timezone;
        }

        $showApprovalButton = false; // default hide
        if (Auth::user()->hasRole(['provider'])) {
            if ($patient->carePlanStatus != 'provider_approved') {
                $showApprovalButton = true;
            }
        } else {
            if ($patient->carePlanStatus == 'draft') {
                $showApprovalButton = true;
            }
        }

        $insurancePolicies = $patient->ccdInsurancePolicies()->get();

        $contact_days_array = [];
        if ($patient->patientInfo()->exists()) {
            $contactWindows = $patient->patientInfo->patientContactWindows;
            $contact_days_array = $contactWindows->pluck('day_of_week')->toArray();
        }

        return view('wpUsers.patient.careplan.patient', compact([
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
        ]));
    }


    /**
     * Save patient add/edit
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function storePatientDemographics(Request $request)
    {
        // input
        $params = new ParameterBag($request->input());
        $patientId = false;
        if ($params->get('user_id')) {
            $patientId = $params->get('user_id');
        }

        // instantiate user
        $user = new User;
        if ($patientId) {
            $user = User::with('phoneNumbers', 'patientInfo', 'careTeamMembers')->find($patientId);
            if (!$user) {
                return response("User not found", 401);
            }
        }

        if ($params->has('insurance')) {
            foreach ($params->get('insurance') as $id => $approved) {
                if (!$approved) {
                    CcdInsurancePolicy::destroy($id);
                    continue;
                }

                $insurance = CcdInsurancePolicy::find($id);
                $insurance->approved = true;
                $insurance->save();
            }
        }

        $userRepo = new UserRepository();

        if ($patientId) {
            $patient = User::where('id', $patientId)->first();
            //Update patient info changes
            $info = $patient->patientInfo;

            if (!$patient->patientInfo) {
                $info = new Patient([
                    'user_id' => $patient->id,
                ]);
            }

            if ($params->get('general_comment')) {
                $info->general_comment = $params->get('general_comment');
            }
            if ($params->get('frequency')) {
                $info->preferred_calls_per_month = $params->get('frequency');
            }
            //we are checking this $info->patientContactWindows()->exists()
            //in case we want to delete all call windows, since $params->get('days') will evaluate to null if we unselect all
            if ($params->get('days') || $info->patientContactWindows()->exists()) {
                PatientContactWindow::sync($info, $params->get('days', []), $params->get('window_start'),
                    $params->get('window_end'));
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
                return redirect($params->get('direction'))->with('messages',
                    ['Successfully updated patient demographics.']);
            }

            return redirect()->back()->with('messages', ['Successfully updated patient demographics.']);
        } else {
            // validate
            $messages = [
                'required'                   => 'The :attribute field is required.',
                'home_phone_number.required' => 'The patient phone number field is required.',
            ];
            $this->validate($request, $user->patient_rules, $messages);
            $role = Role::whereName('participant')->first();
            $newUserId = str_random(15);
            $params->add([
                'username'        => $newUserId,
                'email'           => empty($email = $params->get('email'))
                    ? $newUserId . '@careplanmanager.com'
                    : $email,
                'password'        => $newUserId,
                'user_status'     => '1',
                'program_id'      => $params->get('program_id'),
                'display_name'    => $params->get('first_name') . ' ' . $params->get('last_name'),
                'roles'           => [$role->id],
                'ccm_status'      => 'enrolled',
                'careplan_status' => 'draft',
            ]);
            $newUser = $userRepo->createNewUser($user, $params);

            if ($newUser) {
                //Update patient info changes
                $info = $newUser->patientInfo;
                //in case we want to delete all call windows
                if ($params->get('days') || $info->patientContactWindows()->exists()) {
                    PatientContactWindow::sync($info, $params->get('days', []), $params->get('window_start'),
                        $params->get('window_end'));
                }
                $info->save();

                if ($newUser->carePlan && !$newUser->primaryPractice->settings->isEmpty()) {
                    $newUser->carePlan->mode = $newUser->primaryPractice->settings->first()->careplan_mode;
                    $newUser->carePlan->save();
                }
            }

            return redirect(\URL::route('patient.demographics.show', ['patientId' => $newUser->id]))->with('messages',
                ['Successfully created new patient with demographics.']);
        }
    }


    /**
     * Display patient careteam edit
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showPatientCareteam(
        Request $request,
        $patientId = false
    ) {
        $messages = \Session::get('messages');

        $user = new User;
        if ($patientId) {
            $user = User::find($patientId);
            if (!$user) {
                return response("User not found", 401);
            }
        }
        $patient = $user;

        // get program
        $programId = $user->program_id;

        // care team vars
        $careTeamUserIds = $user->careTeam;
        $ctmsa = $user->sendAlertTo;
        $ctbp = $user->billingProviderID;
        $ctlc = $user->leadContactID;


        //dd($userConfig);

        $careTeamUsers = [];
        if (!empty($careTeamUserIds)) {
            if ((@unserialize($careTeamUserIds) !== false)) {
                $careTeamUserIds = unserialize($careTeamUserIds);
            }
            if (is_array($careTeamUserIds)) {
                foreach ($careTeamUserIds as $id) {
                    $user = User::find($id);
                    if ($user) {
                        $careTeamUsers[] = User::find($id);
                    }
                }
            }
            if (is_int($careTeamUserIds)) {
                if ($user) {
                    $careTeamUsers[] = User::find($careTeamUserIds);
                }
            }

        }

        // get providers
        $providers = [];
        $providers = User::with('phoneNumbers', 'providerInfo')
            ->whereHas('practices', function ($q) use (
                $patient
            ) {
                $q->whereIn('program_id', $patient->viewableProgramIds());
            })
            ->whereHas('roles', function ($q) {
                $q->where('name', '=', 'provider');
            })
            ->orderby('display_name')
            ->get();

        $phtml = '';

        $showApprovalButton = false;
        if (Auth::user()->hasRole('provider')) {
            if ($patient->carePlanStatus != 'provider_approved') {
                $showApprovalButton = true;
            }
        } else {
            if ($patient->carePlanStatus == 'draft') {
                $showApprovalButton = true;
            }
        }

        return view('wpUsers.patient.careplan.careteam', compact([
            'program',
            'patient',
            'messages',
            'sectionHtml',
            'phtml',
            'providers',
            'careTeamUsers',
            'showApprovalButton',
        ]));
    }


    /**
     * Save patient careteam edit
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function storePatientCareteam(Request $request)
    {
        // input
        $params = new ParameterBag($request->input());
        if ($params->get('user_id')) {
            $patientId = $params->get('user_id');
        }

        // instantiate user
        $patient = User::with('phoneNumbers', 'patientInfo', 'careTeamMembers')->find($patientId);
        if (!$patient) {
            return response("Patient user not found", 401);
        }

        // process form
        if ($params->get('formSubmit') == "Save") {
            if ($params->get('ctmCountArr')) {
                if (!empty($params->get('ctmCountArr'))) {
                    // get provider specific info
                    $careTeamUserIds = [];
                    foreach ($_POST['ctmCountArr'] as $ctmCount) {
                        if ($params->get('ctm' . $ctmCount . 'provider') && !empty($params->get('ctm' . $ctmCount . 'provider'))) {
                            $careTeamUserIds[] = $params->get('ctm' . $ctmCount . 'provider');
                        } else {
                            return redirect()->back()->withErrors(['No provider selected for member.']);
                        }
                    }
                    $patient->careTeam = $careTeamUserIds;

                    // set send alerts
                    if ($params->get('ctmsa') && !empty($params->get('ctmsa'))) {
                        $patient->sendAlertTo = $params->get('ctmsa');
                    } else {
                        $patient->sendAlertTo = '';
                    }

                    // set billing provider
                    if ($params->get('ctbp') && !empty($params->get('ctbp'))) {
                        $patient->billingProviderID = $params->get('ctbp');
                    } else {
                        $patient->billingProviderID = '';
                    }

                    // set lead contact
                    if ($params->get('ctlc') && !empty($params->get('ctlc'))) {
                        $patient->leadContactID = $params->get('ctlc');
                    } else {
                        $patient->leadContactID = '';
                    }
                    $patient->save();
                }
            }
        }

        if ($params->get('direction')) {
            return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care team.']);
        }

        return redirect()->back()->with('messages', ['Successfully updated patient care team.']);
    }


    /**
     * Display patient careplan
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function showPatientCareplan(
        Request $request,
        $patientId = false,
        $page,
        CarePlanViewService $carePlanService,
        CpmProblemService $problemService,
        UserService $userService
    ) {
        $messages = \Session::get('messages');

        //variable names to be passed to compact()
        $pageViewVars = [];

        if (empty($patientId)) {
            return response("User not found", 401);
        }

        $patient = User::find($patientId);

        if (empty($patient)) {
            abort(404, 'Patient not found');
        }

        $carePlan = $userService->firstOrDefaultCarePlan($patient);

        $problems = $carePlanService->getProblemsToMonitor($patient);

        // determine which sections to show
        if ($page == 1) {
            $pageViewVars = $carePlanService->carePlanFirstPage($carePlan, $patient);
        } else {
            if ($page == 2) {
                $pageViewVars = $carePlanService->carePlanSecondPage($carePlan, $patient);
            } else {
                if ($page == 3) {
                    $pageViewVars = $carePlanService->carePlanThirdPage($carePlan, $patient);
                }
            }
        }

        if ($patient->carePlanStatus == 'qa_approved' && auth()->user()->canApproveCarePlans()) {
            $showApprovalButton = true;
        } elseif ($patient->carePlanStatus == 'draft' && auth()->user()->can('care-plan-qa-approve')) {
            $showApprovalButton = true;
        } else {
            $showApprovalButton = false;
        }

        $defaultViewVars = compact([
            'page',
            'patient',
            'editMode',
            'carePlan',
            'messages',
            'showApprovalButton',
            'problems',
            'ccdAllergies',
            'ccdProblems',
            'ccdMedications',
        ]);

        return view('wpUsers.patient.careplan.careplan', array_merge($defaultViewVars, $pageViewVars));

    }

    /**
     * Store patient careplan
     *
     * @param  int $patientId
     *
     * @return Response
     */
    public function storePatientCareplan(
        Request $request,
        CpmBiometricService $biometricService,
        CpmLifestyleService $lifestyleService,
        CpmMedicationGroupService $medicationGroupService,
        CpmMiscService $miscService,
        CpmProblemService $problemService,
        CpmSymptomService $symptomService
    ) {
        // input
        $params = new ParameterBag($request->input());

        $direction = $params->get('direction');
        $page = (int)$params->get('page');
        $patientId = $params->get('user_id');

        $instructions = $params->get('instructions', []);


        if (empty($patientId)) {
            return response("User not found", 401);
        }
        if (empty($page)) {
            return response("Page not found", 401);
        }

        $user = User::find($patientId);

        if (empty($user)) {
            abort(404, 'Patient not found');
        }

        if ($page == 1) {
            //get cpm entities or empty array
            $cpmLifestyles = $params->get('cpmLifestyles', []);
            $cpmMedicationGroups = $params->get('cpmMedicationGroups', []);
            $cpmMiscs = $params->get('cpmMiscs', []);
            $cpmProblems = $params->get('cpmProblems', []);

            $lifestyleService->syncWithUser($user, $cpmLifestyles, $page, $instructions);
            $medicationGroupService->syncWithUser($user, $cpmMedicationGroups, $page, $instructions);
            $miscService->syncWithUser($user, $cpmMiscs, $page, $instructions);
            $problemService->syncWithUser($user, $cpmProblems, $page, $instructions);
        }

        if ($page == 2) {
            //get cpm entities or empty array
            $cpmBiometrics = $params->get('cpmBiometrics', []);
            $cpmMiscs = $params->get('cpmMiscs', []);

            $biometricService->syncWithUser($user, $cpmBiometrics, $page, $instructions);
            $miscService->syncWithUser($user, $cpmMiscs, $page, $instructions);

            $biometricsValues = $params->get('biometrics', []);

            //weight
            if (!isset($biometricsValues['weight']['monitor_changes_for_chf'])) {
                $biometricsValues['weight']['monitor_changes_for_chf'] = 0;
            }
            if (!empty($biometricsValues['weight']['starting']) || !empty($biometricsValues['weight']['target'])) {
                $validator = \Validator::make($biometricsValues['weight'], CpmWeight::$rules, CpmWeight::$messages);

                if ($validator->fails()) {
                    return redirect()
                        ->back()
                        ->withErrors($validator)
                        ->withInput();
                }

                CpmWeight::updateOrCreate([
                    'patient_id' => $user->id,
                ], $biometricsValues['weight']);
            }

            //blood sugar
            if (isset($biometricsValues['bloodSugar'])) {
                if (!empty($biometricsValues['bloodSugar']['starting']) || !empty($biometricsValues['bloodSugar']['starting_a1c']) || !empty($biometricsValues['bloodSugar']['target'])) {
                    $validator = \Validator::make($biometricsValues['bloodSugar'], CpmBloodSugar::$rules,
                        CpmBloodSugar::$messages);

                    if ($validator->fails()) {
                        return redirect()
                            ->back()
                            ->withErrors($validator)
                            ->withInput();
                    }

                    CpmBloodSugar::updateOrCreate([
                        'patient_id' => $user->id,
                    ], $biometricsValues['bloodSugar']);
                }
            }


            //blood pressure
            if (isset($biometricsValues['bloodPressure'])) {
                if (!empty($biometricsValues['bloodPressure']['starting']) || !empty($biometricsValues['bloodPressure']['target'])) {
                    $validator = \Validator::make($biometricsValues['bloodPressure'], CpmBloodPressure::$rules,
                        CpmBloodPressure::$messages);

                    $validStarting = validateBloodPressureString($biometricsValues['bloodPressure']['starting']);
                    $validTarget = validateBloodPressureString($biometricsValues['bloodPressure']['target']);

                    if (!$validStarting || !$validTarget) {
                        return redirect()
                            ->back()
                            ->withErrors(['Systolic and Diastolic Blood Pressure must be between 2 and 3 digits'])
                            ->withInput();
                    }

                    if ($validator->fails()) {
                        return redirect()
                            ->back()
                            ->withErrors($validator)
                            ->withInput();
                    }
                    CpmBloodPressure::updateOrCreate([
                        'patient_id' => $user->id,
                    ], $biometricsValues['bloodPressure']);
                }
            }

            //smoking
            if (isset($biometricsValues['smoking'])) {
                if (!empty($biometricsValues['smoking']['starting']) || !empty($biometricsValues['smoking']['target'])) {
                    $validator = \Validator::make($biometricsValues['smoking'], CpmSmoking::$rules,
                        CpmSmoking::$messages);

                    if ($validator->fails()) {
                        return redirect()
                            ->back()
                            ->withErrors($validator)
                            ->withInput();
                    }

                    CpmSmoking::updateOrCreate([
                        'patient_id' => $user->id,
                    ], $biometricsValues['smoking']);
                }
            }
        }

        if ($page == 3) {
            //get cpm entities or empty array
            $cpmMiscs = $params->get('cpmMiscs', []);
            $cpmSymptoms = $params->get('cpmSymptoms', []);

            $miscService->syncWithUser($user, $cpmMiscs, $page, $instructions);
            $symptomService->syncWithUser($user, $cpmSymptoms, $page, $instructions);

            //use the url to see if approve careplan was pressed
            $urlString = parse_url($direction);

            if (array_key_exists('query', $urlString)) {
                parse_str($urlString['query'], $queryString);

                if (array_key_exists('markAsApproved', $queryString)) {
                    if ($queryString['markAsApproved']) {
                        event(new CarePlanWasApproved($user));
                    }
                }
            }
        }

        if ($direction) {
            return redirect($direction)->with('messages', ['Successfully updated patient care plan.']);
        }

        return redirect()->back()->with('messages', ['successfully updated patient care plan']);
    }

    public function switchToWebMode($carePlanId)
    {
        $cp = CarePlan::find($carePlanId);

        $cp->mode = CarePlan::WEB;
        $cp->save();

        return redirect()->route('patient.careplan.print', ['patientId' => $cp->user_id]);
    }
}
