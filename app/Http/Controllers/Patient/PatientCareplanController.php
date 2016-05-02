<?php namespace App\Http\Controllers\Patient;

use App\CareItem;
use App\Services\ReportsService;
use App\Activity;
use App\CareSection;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\Observation;
use App\CarePlan;
use App\CarePlanItem;
use App\WpBlog;
use App\Location;
use App\User;
use App\UserMeta;
use App\CPRulesPCP;
use App\Role;
use App\Services\CareplanUIService;
use App\Services\MsgCPRules;
use App\Services\ObservationService;
use App\CLH\Repositories\UserRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTimeZone;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Support\Facades\URL;
use PasswordHash;
use Symfony\Component\HttpFoundation\ParameterBag;
use Input;
use Auth;
use DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PatientCareplanController extends Controller
{

    //Show Patient Careplan Print List  (URL: /manage-patients/careplan-print-list)
    public function index(Request $request)
    {
        $patientData = array();
        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
            ->with('phoneNumbers', 'patientInfo', 'patientCareTeamMembers')
            ->select(DB::raw('users.*'))
            ->get();

        // get approvers before
        $approvers = null;
        $approverIds = array();
        if($patients->count() > 0) {
            foreach ($patients as $patient) {
                if ($patient->carePlanStatus  == 'provider_approved') {
                    $approverId = $patient->carePlanProviderApprover;
                    if(!empty($approverId) && !in_array($approverId, $approverIds)) {
                        $approverIds[] = $approverId;
                    }
                }
            }
            $approvers = User::whereIn('ID', $approverIds)->get();
        }

        if ($patients->count() > 0) {
            $foundUsers = array(); // save resources, no duplicate db calls
            $foundPrograms = array(); // save resources, no duplicate db calls
            foreach ($patients as $patient) {
                // skip if patient has no name
                if (empty($patient->first_name)) {
                    continue 1;
                }
                $last_printed = $patient->careplan_last_printed;
                if ($last_printed) {
                    $printed_status = 'Yes';
                    $printed_date = $last_printed;
                } else {
                    $printed_status = 'No';
                    $printed_date = null;
                }
                ($last_printed) ? $printed = $last_printed : $printed = 'No';

                // careplan status stuff from 2.x
                $careplanStatus = $patient->carePlanStatus;
                $careplanStatusLink = '';
                $approverName = 'NA';
                $tooltip = 'NA';

                if ($patient->carePlanStatus  == 'provider_approved') {
                    $approverId = $patient->carePlanProviderApprover;
                    if($approverId == 5) {
                        //dd($approvers->where('ID', $approverId)->first());
                    }
                    $approver = $approvers->where('ID', $approverId)->first();
                    if(!$approver) {
                        if(!empty($approverId)) {
                            if(!isset($foundUsers[$approverId])) {
                                $approver = User::find($approverId);
                                $foundUsers[$approverId] = $approver;
                            } else {
                                $approver = $foundUsers[$approverId];
                            }
                        }
                    }
                    if($approver) {
                        $approverName = $approver->fullName;
                        $careplanStatus = 'Approved';
                        $careplanStatusLink = '<span data-toggle="" title="' . $approver->fullName . ' ' . $patient->carePlanProviderDate . '">Approved</span>';
                        $tooltip = $approverName . ' ' . $patient->carePlanProviderDate;
                    }
                } else if ($patient->carePlanStatus == 'qa_approved') {
                    $careplanStatus = 'Approve Now';
                    $tooltip = $careplanStatus;
                    $careplanStatusLink = 'Approve Now';
                    if (Auth::user()->can(['is-provider'])) {
                        $careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show', array('patient' => $patient->ID)) . '"><strong>Approve Now</strong></a>';
                    }
                } else if ($patient->carePlanStatus == 'draft') {
                    $careplanStatus = 'CLH Approve';
                    $tooltip = $careplanStatus;
                    $careplanStatusLink = 'CLH Approve';
                    if (Auth::user()->can(['is-care-center']) || Auth::user()->can(['is-administrator'])) {
                        $careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show', array('patient' => $patient->ID)) . '"><strong>CLH Approve</strong></a>';
                    }
                }

                // get billing provider name
                $programName = '';
                $bpName = '';
                $bpID = $patient->billingProviderID;
                if(!isset($foundPrograms[$patient->program_id])) {
                    $program = WpBlog::find($patient->program_id);
                    if($program) {
                        $foundPrograms[$patient->program_id] = $program;
                        $programName = $program->display_name;
                    }
                } else {
                    $program = $foundPrograms[$patient->program_id];
                    $programName = $program->display_name;
                }

                if(!empty($bpID)) {
                    if(!isset($foundUsers[$bpID])) {
                        $bpUser = User::find($bpID);
                        if($bpUser) {
                            $bpName = $bpUser->fullName;
                            $foundUsers[$bpID] = $bpUser;
                        }
                    } else {
                        $bpUser = $foundUsers[$bpID];
                        $bpName = $bpUser->fullName;
                    }
                }

                $patientData[] = array(
                    'key' => $patient->ID,
                    'id' => $patient->ID,
                    'patient_name' => $patient->fullName,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'careplan_status' => $careplanStatus,
                    'careplan_status_link' => $careplanStatusLink,
                    'careplan_provider_approver' => $approverName,
                    'dob' => Carbon::parse($patient->birthDate)->format('m/d/Y'),
                    'phone' => $patient->phone,
                    'age' => $patient->age,
                    'reg_date' => Carbon::parse($patient->registrationDate)->format('m/d/Y'),
                    'last_read' => '',
                    'ccm_time' => $patient->patientInfo->cur_month_activity_time,
                    'ccm_seconds' => $patient->patientInfo->cur_month_activity_time,
                    'provider' => $bpName,
                    'program_name' => $programName,
                    'careplan_last_printed' => $printed_date,
                    'careplan_printed' => $printed_status
                );
            }
        }
        debug($patientData);

        $patientJson = json_encode($patientData);
        return view('wpUsers.patient.careplan.printlist', compact(['pendingApprovals', 'patientJson']));
    }

    public function printMultiCareplan(Request $request)
    {
        if (!$request['users']) {
            return response()->json("Something went wrong..");
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
        $careplans = $reportService->carePlanGenerator($users);
        return view('wpUsers.patient.multiview', compact(['careplans']));
    }

    /**
     * Display patient add/edit
     *
     * @param  int $patientId
     * @return Response
     */
    public function showPatientDemographics(Request $request, $patientId = false)
    {
        $messages = \Session::get('messages');

        // determine if existing user or new user
        $user = new User;
        $programId = false;
        if ($patientId) {
            $user = User::with('patientInfo')->find($patientId);
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
        $programs = WpBlog::whereIn('blog_id', Auth::user()->viewableProgramIds())->lists('display_name', 'blog_id');

        // roles
        $patientRoleId = Role::where('name', '=', 'participant')->first();
        $patientRoleId = $patientRoleId->id;

        // locations @todo get location id for WpBlog
        $program = WpBlog::find($programId);
        $locations = array();
        if ($program) {
            $locations = Location::where('parent_id', '=', $program->location_id)->lists('name', 'id');
        }

        // care plans
        $carePlans = CarePlan::where('program_id', '=', $programId)->lists('display_name', 'id');

        // States (for dropdown)
        $states = array('AL' => "Alabama", 'AK' => "Alaska", 'AZ' => "Arizona", 'AR' => "Arkansas", 'CA' => "California", 'CO' => "Colorado", 'CT' => "Connecticut", 'DE' => "Delaware", 'DC' => "District Of Columbia", 'FL' => "Florida", 'GA' => "Georgia", 'HI' => "Hawaii", 'ID' => "Idaho", 'IL' => "Illinois", 'IN' => "Indiana", 'IA' => "Iowa", 'KS' => "Kansas", 'KY' => "Kentucky", 'LA' => "Louisiana", 'ME' => "Maine", 'MD' => "Maryland", 'MA' => "Massachusetts", 'MI' => "Michigan", 'MN' => "Minnesota", 'MS' => "Mississippi", 'MO' => "Missouri", 'MT' => "Montana", 'NE' => "Nebraska", 'NV' => "Nevada", 'NH' => "New Hampshire", 'NJ' => "New Jersey", 'NM' => "New Mexico", 'NY' => "New York", 'NC' => "North Carolina", 'ND' => "North Dakota", 'OH' => "Ohio", 'OK' => "Oklahoma", 'OR' => "Oregon", 'PA' => "Pennsylvania", 'RI' => "Rhode Island", 'SC' => "South Carolina", 'SD' => "South Dakota", 'TN' => "Tennessee", 'TX' => "Texas", 'UT' => "Utah", 'VT' => "Vermont", 'VA' => "Virginia", 'WA' => "Washington", 'WV' => "West Virginia", 'WI' => "Wisconsin", 'WY' => "Wyoming");

        // timezones for dd
        $timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($timezones_raw as $timezone) {
            $timezones[$timezone] = $timezone;
        }

        $showApprovalButton = false; // default hide
        if (Auth::user()->can(['is-provider'])) {
            if ($patient->carePlanStatus != 'provider_approved') {
                $showApprovalButton = true;
            }
        } else if ($patient->carePlanStatus == 'draft') {
            $showApprovalButton = true;
        }

        return view('wpUsers.patient.careplan.patient', compact(['patient', 'userMeta', 'userConfig', 'states', 'locations', 'timezones', 'messages', 'patientRoleId', 'programs', 'programId', 'showApprovalButton', 'carePlans']));
    }


    /**
     * Save patient add/edit
     *
     * @param  int $patientId
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
            $user = User::with('phoneNumbers', 'patientInfo', 'patientCareTeamMembers')->find($patientId);
            if (!$user) {
                return response("User not found", 401);
            }
        }

        $userRepo = new UserRepository();

        if ($patientId) {
            // validate
            $messages = [
                'required' => 'The :attribute field is required.',
                'home_phone_number.required' => 'The patient phone number field is required.',
            ];
            $this->validate($request, $user->patient_rules, $messages);
            $userRepo->editUser($user, $params);
            if ($params->get('direction')) {
                return redirect($params->get('direction'))->with('messages', ['Successfully updated patient demographics.']);
            }
            return redirect()->back()->with('messages', ['Successfully updated patient demographics.']);
        } else {
            // validate
            $messages = [
                'required' => 'The :attribute field is required.',
                'home_phone_number.required' => 'The patient phone number field is required.',
            ];
            $this->validate($request, $user->patient_rules, $messages);
            $role = Role::whereName('participant')->first();
            $newUserId = str_random(15);
            $params->add(array(
                'user_login' => $newUserId,
                'user_email' => $newUserId . '@careplanmanager.com',
                'user_pass' => $newUserId,
                'user_status' => '1',
                'user_nicename' => '',
                'program_id' => $params->get('program_id'),
                'display_name' => $params->get('first_name') . ' ' . $params->get('last_name'),
                'roles' => [$role->id],
                'ccm_status' => 'enrolled',
                'careplan_status' => 'draft',
            ));
            $newUser = $userRepo->createNewUser($user, $params);
            return redirect(\URL::route('patient.demographics.show', array('patientId' => $newUser->ID)))->with('messages', ['Successfully created new patient with demographics.']);
        }
    }


    /**
     * Display patient careteam edit
     *
     * @param  int $patientId
     * @return Response
     */
    public function showPatientCareteam(Request $request, $patientId = false)
    {
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

        $careTeamUsers = array();
        if(!empty($careTeamUserIds)) {
            if((@unserialize($careTeamUserIds) !== false)) {
                $careTeamUserIds = unserialize($careTeamUserIds);
            }
            if (is_array($careTeamUserIds)) {
                foreach ($careTeamUserIds as $id) {
                    $user = User::find($id);
                    if($user) {
                        $careTeamUsers[] = User::find($id);
                    }
                }
            }
            if (is_int($careTeamUserIds)) {
                if($user) {
                    $careTeamUsers[] = User::find($careTeamUserIds);
                }
            }

        }

        // get providers
        $providers = array();
        $providers = User::with('phoneNumbers', 'providerInfo')
            ->whereHas('programs', function ($q) use ($patient) {
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
        } else if ($patient->carePlanStatus == 'draft') {
            $showApprovalButton = true;
        }

        return view('wpUsers.patient.careplan.careteam', compact(['program', 'patient', 'messages', 'sectionHtml', 'phtml', 'providers', 'careTeamUsers', 'showApprovalButton']));
    }


    /**
     * Save patient careteam edit
     *
     * @param  int $patientId
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
        $patient = User::with('phoneNumbers', 'patientInfo', 'patientCareTeamMembers')->find($patientId);
        if (!$patient) {
            return response("Patient user not found", 401);
        }

        // process form
        if ($params->get('formSubmit') == "Save") {
            if ($params->get('ctmCountArr')) {
                if (!empty($params->get('ctmCountArr'))) {
                    // get provider specific info
                    $careTeamUserIds = array();
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
     * @return Response
     */
    public function showPatientCareplan(Request $request, $patientId = false, $page)
    {
        $messages = \Session::get('messages');

        $user = false;
        if ($patientId) {
            $user = User::find($patientId);
            if (!$user) {
                return response("User not found", 401);
            }
        }
        $patient = $user;
        $carePlan = CarePlan::where('id', '=', $user->care_plan_id)
            ->first();

        if (!$carePlan) {
            $userRepo = new UserRepository();
            $userRepo->createDefaultCarePlan($user, array());
            $carePlan = CarePlan::where('id', '=', $user->care_plan_id)
                ->first();
        }

        $treating = array();
        if ($carePlan) {
            $carePlan->build($user->ID);

            //problems for userheader
            $treating = (new ReportsService())->getProblemsToMonitorWithDetails($carePlan);
        }

        // determine which sections to show
        if ($page == 1) {
            $careSectionNames = array(
                'diagnosis-problems-to-monitor',
                'lifestyle-to-monitor',
                'medications-to-monitor',
            );
        } else if ($page == 2) {
            $careSectionNames = array(
                'biometrics-to-monitor',
                'transitional-care-management',
            );
        } else if ($page == 3) {
            $careSectionNames = array(
                'symptoms-to-monitor',
                'additional-information',
                //'misc',
            );
        }
        $editMode = false;

        $showApprovalButton = false;
        if (Auth::user()->can(['is-provider'])) {
            if ($patient->carePlanStatus != 'provider_approved') {
                $showApprovalButton = true;
            }
        } else if ($patient->carePlanStatus == 'draft') {
            $showApprovalButton = true;
        }

        return view('wpUsers.patient.careplan.careplan', compact(['page', 'careSectionNames', 'patient', 'editMode', 'carePlan', 'messages', 'showApprovalButton', 'treating']));
    }

    /**
     * Store patient careplan
     *
     * @param  int $patientId
     * @return Response
     */
    public function storePatientCareplan(Request $request)
    {

        $observationService = new ObservationService;
        $msgCPRules = new MsgCPRules;

        // input
        $params = new ParameterBag($request->input());

        if ($params->get('user_id')) {
            $patientId = $params->get('user_id');
        }

        // instantiate user
        $user = User::with('phoneNumbers', 'patientInfo', 'patientCareTeamMembers')->find($patientId);
        if (!$user) {
            return response("User not found", 401);
        }

        // get page
        $page = $params->get('page');
        if ($page == 3) {
            // check for approval here
            // should we update careplan_status?
            if ($user->carePlanStatus != 'provider_approved') {
                if (Auth::user()->can(['is-provider'])) {
                    $user->carePlanStatus = 'provider_approved'; // careplan_status
                    $user->carePlanProviderApprover = Auth::user()->ID; // careplan_provider_approver
                    $user->carePlanProviderApproverDate = date('Y-m-d H:i:s'); // careplan_provider_date

                    //Creating Reports for Aprima API
                    //      Since there isn't a way to get the provider's location,
                    //      we assume the patient's location and check it that
                    //      is a child of Aprima's Location.
                    $locationId = $user->getpreferredContactLocationAttribute();

                    $locationObj = Location::find($locationId);

                    if(!empty($locationObj) && $locationObj->parent_id == Location::APRIMA_ID){
                        (new ReportsService())->createPatientReport($user, $user->getCarePlanProviderApproverAttribute());
                    }


                } else {
                    $user->carePlanStatus = 'qa_approved'; // careplan_status
                    $user->carePlanQaApprover = Auth::user()->ID; // careplan_qa_approver
                    $user->carePlanQaDate = date('Y-m-d H:i:s'); // careplan_qa_date
                }
                $user->save();
            }
        }

        // get carePlan
        $careplan = CarePlan::find($params->get('careplan_id'));
        if (!$careplan) {
            if ($params->get('direction')) {
                return redirect($params->get('direction'))->with('messages', ['No care plan found to update.']);
            }
            return redirect()->back()->with('errors', ['No care plan found to update.']);
        }

        // loop through care plan items in viewed sections
        if ($params->get('careSections')) {
            $sectionCareItems = $careplan->careItems()->whereIn('section_id', $params->get('careSections'))->get();
            foreach ($sectionCareItems as $careItem) {
                $carePlanItem = CarePlanItem::where('item_id', '=', $careItem->id)
                    ->where('plan_id', '=', $careplan->id)
                    ->first();
                if (!$carePlanItem) {
                    continue 1;
                }
                $value = $params->get('item|' . $carePlanItem->id);
                // if checkbox and unchecked on the ui it doesnt post, so set these to Inactive
                if (!$value && ($carePlanItem->ui_fld_type == 'SELECT' || $carePlanItem->ui_fld_type == 'CHECK')) {
                    $value = 'Inactive';
                }
                // update user item
                if ($value) {
                    // process starting observations
                    if($carePlanItem->ui_track_as_observation == 'starting') {
                        if(empty($carePlanItem->careItem->parent_id)) {
                            continue 1;
                        }
                        // get parent item
                        $parentCareItem = CareItem::where('id', '=', $carePlanItem->careItem->parent_id)->first();
                        if(empty($parentCareItem)) {
                            continue 1;
                        }

                        // set vars
                        $obsMessageId = $parentCareItem->question->msg_id;
                        $qsType  = $msgCPRules->getQsType($obsMessageId, $user->program_id);
                        $obsKey = $parentCareItem->obs_key;

                        // validate answer
                        $answerResponse =  $msgCPRules->getValidAnswer($user->program_id, $qsType, $obsMessageId, $value, false);
                        if(!$answerResponse) {
                            return redirect()->back()->withErrors(['You entered an invalid value for ' . $carePlanItem->careItem->display_name . ', please review and resubmit.'])->withInput();
                        }

                        // update/store observation
                        $observationService->storeObservationFromApp($user->ID, 0, $value, date("Y-m-d H:i:s"), $obsMessageId, $obsKey, 'America/New_York', 'ov_reading', 'Y');

                    }

                    // update user value
                    $carePlanItem->meta_value = $careplan->setCareItemUserValue($user, $carePlanItem->careItem->name, $value);
                }
            }
        }

        if ($params->get('direction')) {
            return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care plan.']);
        }
        return redirect()->back()->with('messages', ['successfully updated patient care plan']);
    }

    /**
     * Display patient careplan
     *
     * @param  int $patientId
     * @return Response
     */
    /*
    public function showPatientCareplanWP(Request $request, $patientId = false)
    {
        $messages = \Session::get('messages');

        $wpUser = false;
        if($patientId) {
            $wpUser = User::find($patientId);
            if (!$wpUser) {
                return response("User not found", 401);
            }
        }
        $patient = $wpUser;

        // program
        $program = WpBlog::find($wpUser->program_id);

        $params = $request->all();

        // user config
        $userConfig = (new UserConfigTemplate())->getArray();
        if(isset($userMeta['wp_' . $wpUser->program_id . '_user_config'])) {
            $userConfig = unserialize($userMeta['wp_' . $wpUser->program_id . '_user_config']);
            $userConfig = array_merge((new UserConfigTemplate())->getArray(), $userConfig);
        }

        //$sectionHtml = $carePlanUI->renderCareplanSection($wpUser, 'Biometrics to Monitor');
        $sectionHtml = (new CareplanUIService)->renderCareplanSections(array(), $wpUser->program_id, $wpUser);

        return view('wpUsers.patient.careplan.careplan', compact(['program','patient', 'userConfig', 'messages', 'sectionHtml']));
    }
    */

    /**
     * Save patient careplan
     *
     * @param  int $patientId
     * @return Response
     */
    /*
    public function storePatientCareplanWP(Request $request)
    {
        // input
        $params = new ParameterBag($request->input());
        if($params->get('user_id')) {
            $patientId = $params->get('user_id');
        }

        // instantiate user
        $wpUser = User::with('meta')->find($patientId);
        if (!$wpUser) {
            return response("User not found", 401);
        }

        if($params->get('direction')) {
            return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care plan.']);
        }
        return redirect()->back()->with('messages', ['successfully updated patient care plan']);

        //return view('wpUsers.patient.careplan', ['program' => $program, 'patient' => $wpUser]);
    }
    */
}
