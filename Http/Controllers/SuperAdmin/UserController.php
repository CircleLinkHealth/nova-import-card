<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use CircleLinkHealth\SharedModels\Entities\Note;
use Auth;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use DateTimeZone;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserController extends Controller
{
    const ROLES_TO_HIDE_FROM_ADMIN_PANEL = [
        'participant',
        'care-center-external',
        'med_assistant',
        'provider',
        'api-ccd-vendor',
        'api-data-consumer',
        'no-access',
        'office-admin',
        'practice-lead',
        'office_admin',
        'registered-nurse',
        'no-ccm-care-center',
        'specialist',
    ];

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $messages = \Session::get('messages');

        $wpUser = new User();

        $roles = Role::whereNotIn(
            'name',
            self::ROLES_TO_HIDE_FROM_ADMIN_PANEL
        )->orderBy('display_name')->pluck('display_name', 'id')->all();

        // set role
        $wpRole = '';

        // States (for dropdown)
        $states_arr = usStatesArrayForDropdown();

        // programs for dd
        $wpBlogs = Practice::orderBy('id', 'desc')->pluck('display_name', 'id')->all();

        $locations = Location::all()->pluck('name', 'id')->all();

        // timezones for dd
        $timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($timezones_raw as $timezone) {
            $timezones_arr[$timezone] = $timezone;
        }

        // providers
        $providers_arr = [
            'provider'          => 'provider',
            'office_admin'      => 'office_admin',
            'participant'       => 'participant',
            'care_center'       => 'care_center',
            'viewer'            => 'viewer',
            'clh_participant'   => 'clh_participant',
            'clh_administrator' => 'clh_administrator',
        ];

        // display view
        return view(
            'wpUsers.create',
            [
                'wpUser'        => $wpUser,
                'states_arr'    => $states_arr,
                'timezones_arr' => $timezones_arr,
                'wpBlogs'       => $wpBlogs,
                'providers_arr' => $providers_arr,
                'messages'      => $messages,
                'roles'         => $roles,
                'locations'     => $locations,
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if ( ! $user) {
            return response('User not found', 401);
        }

        //$user->practices()->detach();
        $user->delete();

        return redirect()->back()->with('messages', ['successfully deleted user']);
    }

    /**
     * Perform actions on multiple users.
     *
     * @return Response
     */
    public function doAction(Request $request)
    {
        $params = new ParameterBag($request->input());

        $action = $params->get('action');

        if ( ! $action) {
            return redirect()->back()->withErrors(['form_error' => "There was an error: Missing 'action' parameter."]);
        }

        $selectAllFromFilters = ! empty($params->get('filterRole')) || ! empty($params->get('filterProgram'));
        if ($selectAllFromFilters) {
            $users = $this->getUsersBasedOnFilters($params);
        } else {
            $users = $params->get('users');
        }

        if (empty($users)) {
            return redirect()->back()->withErrors(['form_error' => 'There was an error: Users array is empty.']);
        }

        if ('withdraw' == $action) {
            $withdrawnReason = $params->get('withdrawn-reason');
            if ('Other' == $withdrawnReason) {
                $withdrawnReason = $params->get('withdrawn-reason-other');
            }
            $this->withdrawUsers($users, $withdrawnReason);

            return redirect()->back()->with('messages', ['Action [Withdraw] was successful']);
        }
        if ('unreachable' == $action) {
            $markedUnreachable = $this->markPatientsAsUnreachable($users);

            return redirect()->back()->with('messages', ["Marked $markedUnreachable patient(s) as ".ucfirst(Patient::UNREACHABLE)]);
        }
        if ('enroll' == $action) {
            $enrolled = $this->enrollPatients($users);

            return redirect()->back()->with('messages', ["Marked $enrolled patient(s) as ".ucfirst(Patient::ENROLLED)]);
        }

        return redirect()->back()->withErrors(['form_error' => "Unhandled action: ${action}"]);

        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit(
        Request $request,
        $id
    ) {
        $messages = \Session::get('messages');

        $user = User::with(['practices', 'roles'])->has('roles')->findOrFail($id);

        if ($user->isParticipant()) {
            return redirect()->route('patient.demographics.show', [$user->id]);
        }

        if ($user->roles->whereNotIn('name', self::ROLES_TO_HIDE_FROM_ADMIN_PANEL)->isEmpty()) {
            $roles = Role::whereIn('name', self::ROLES_TO_HIDE_FROM_ADMIN_PANEL)->orderBy('display_name')->get(
                ['display_name', 'id']
            )->implode('display_name', ', ');
            $message = 'Users with any of the following roles cannot be edited from this page:'.PHP_EOL.$roles.'.';
            abort(404, $message);
        }

        $roles = Role::whereNotIn('name', self::ROLES_TO_HIDE_FROM_ADMIN_PANEL)->orderBy('display_name')->pluck(
            'display_name',
            'id'
        )->all();
        $role = $user->roles->first();
        if ( ! $role) {
            $role = Role::first();
        }

        // build revision info
        $revisions = [];

        // first for user
        $revisionHistory = collect([]);
        foreach ($user->revisionHistory->sortByDesc('updated_at')->take(10) as $history) {
            $revisionHistory->push($history);
        }
        $revisions['User'] = $revisionHistory;

        // patientInfo
        if ('participant' == $role->name) {
            $revisionHistory = collect([]);

            if ($user->patientInfo) {
                foreach ($user->patientInfo->revisionHistory->sortByDesc('updated_at')->take(10) as $history) {
                    $revisionHistory->push($history);
                }
            }

            $revisions['Patient Info'] = $revisionHistory;
        }

        $params = $request->all();
        if ( ! empty($params)) {
            if (isset($params['action'])) {
                if ('impersonate' == $params['action']) {
                    Auth::login($id);

                    return redirect()->route('/', [])->with('messages', ['Logged in as user '.$id]);
                }
            }
        }

        // locations @todo get location id for Practice
        $practice      = Practice::find($user->program_id);
        $locations_arr = [];
        if ($practice) {
            $locations_arr = $practice->locations->all();
        }

        // States (for dropdown)
        $states_arr = usStatesArrayForDropdown();

        // programs for dd
        $wpBlogs = Practice::orderBy('id', 'desc')->pluck('display_name', 'id')->all();

        // timezones for dd
        $timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($timezones_raw as $timezone) {
            $timezones_arr[$timezone] = $timezone;
        }

        // providers
        $providers_arr = [
            'provider'          => 'provider',
            'office_admin'      => 'office_admin',
            'participant'       => 'participant',
            'care_center'       => 'care_center',
            'viewer'            => 'viewer',
            'clh_participant'   => 'clh_participant',
            'clh_administrator' => 'clh_administrator',
        ];

        // display view
        return view(
            'wpUsers.edit',
            [
                'patient'       => $user,
                'locations_arr' => $locations_arr,
                'states_arr'    => $states_arr,
                'timezones_arr' => $timezones_arr,
                'wpBlogs'       => $wpBlogs,
                'primaryBlog'   => $user->program_id,
                'providers_arr' => $providers_arr,
                'messages'      => $messages,
                'role'          => $role,
                'roles'         => $roles,
                'revisions'     => $revisions,
                'userPractices' => $user->practices->pluck('id')->all(),
            ]
        );
    }

    public static function hideFromAdminPanel($wpUser)
    {
        return in_array($wpUser->roles->first()->name, self::ROLES_TO_HIDE_FROM_ADMIN_PANEL);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        ini_set('memory_limit', '200M');

        $messages = \Session::get('messages');

        $missingProgramId = [];

        // display view
        $wpUsers = User::orderBy('id', 'desc');

        // FILTERS
        $params = $request->all();

        // filter user
        $users = User::whereIn('id', Auth::user()->viewableUserIds())
            ->orderBy('id', 'desc')
            ->get()
            ->mapWithKeys(
                function ($user) {
                    return [
                        $user->id => "{$user->getFirstName()} {$user->getLastName()} ({$user->id})",
                    ];
                }
            )
            ->all();

        $filterUser = 'all';

        if ( ! empty($params['filterUser'])) {
            $filterUser = $params['filterUser'];
            if ('all' != $params['filterUser']) {
                $wpUsers->where('id', '=', $filterUser);
            }
        }

        // role filter
        $roles = Role::all()
            ->pluck('display_name', 'name')
            ->all();

        $filterRole = 'all';

        if ( ! empty($params['filterRole'])) {
            $filterRole = $params['filterRole'];
            if ('all' != $params['filterRole']) {
                $wpUsers->ofType($filterRole);
            }
        }

        // program filter
        $programs = Practice::orderBy('id', 'desc')
            ->whereIn('id', Auth::user()->viewableProgramIds())
            ->get()
            ->pluck('display_name', 'id')
            ->all();

        $filterProgram = 'all';

        if ( ! empty($params['filterProgram'])) {
            $filterProgram = $params['filterProgram'];
            if ('all' != $params['filterProgram']) {
                $wpUsers->where('program_id', '=', $filterProgram);
            }
        }

        // only let owners see owners
        if ( ! Auth::user()->hasRole(['administrator'])) {
            $wpUsers = $wpUsers->whereHas(
                'roles',
                function ($q) {
                    $q->where('name', '!=', 'administrator');
                }
            );
            // providers can only see their participants
            if (Auth::user()->isProvider()) {
                $wpUsers->whereHas(
                    'roles',
                    function ($q) {
                        $q->whereHas(
                            'perms',
                            function ($q2) {
                                $q2->where('name', '=', 'is-participant');
                            }
                        );
                    }
                );
                $wpUsers->where('program_id', '=', Auth::user()->program_id);
            }
        }

        $queryString = $request->query();

        // patient restriction
        $wpUsers->whereIn('id', Auth::user()->viewableUserIds());
        $wpUsers      = $wpUsers->paginate(20);
        $invalidUsers = [];

        return view(
            'wpUsers.index',
            compact(
                [
                    'messages',
                    'wpUsers',
                    'users',
                    'filterUser',
                    'programs',
                    'filterProgram',
                    'roles',
                    'filterRole',
                    'invalidUsers',
                    'queryString',
                ]
            )
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show(
        Request $request,
        $id
    ) {
        dd('user /edit to view user info');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params = new ParameterBag($request->input());
        if ( ! $params->has('roles') && $params->has('role')) {
            $params->add([
                'roles' => [$params->get('role')],
            ]);
        }
        $wpUser = (new UserRepository())->createNewUser($params);

        if ($request->has('provider_id')) {
            $wpUser->setBillingProviderId($request->input('provider_id'));
        }

        //if location was selected save it
        if (is_numeric($locationId = $request->input('location_id'))) {
            $wpUser->locations()->attach(Location::find($locationId));
        }

        return redirect()->route('admin.users.edit', [$wpUser->id])->with(
            'messages',
            ['successfully created new user - '.$wpUser->id]
        );
    }

    public function storeQuickPatient()
    {
        if ( ! Auth::user()->isAdmin()) {
            abort(403);
        }
        $wpUser = new User();

        // create participant here

        return redirect()->route('admin.users.edit', [$wpUser->id])->with(
            'messages',
            ['successfully created new user - '.$wpUser->id]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update(
        Request $request,
        $id
    ) {
        $wpUser = User::whereHas(
            'roles',
            function ($q) {
                $q->whereNotIn('name', self::ROLES_TO_HIDE_FROM_ADMIN_PANEL);
            }
        )->findOrFail($id);
        if ( ! $wpUser) {
            return response('User not found', 401);
        }

        // input
        $params = new ParameterBag($request->input());

        $userRepo = new UserRepository();

        $userRepo->editUser($wpUser, $params);

        if ($request->has('provider_id')) {
            $wpUser->setBillingProviderid($request->input('provider_id'));
        }

        if ($request->has('provider_id')) {
            $wpUser->setBillingProviderid($request->input('provider_id'));
        }

        $wpUser->setCanSeePhi($request->has('can_see_phi'));

        return redirect()->back()->with('messages', ['successfully updated user']);
    }

    private function enrollPatients(array $userIds)
    {
        return Patient::whereIn('user_id', $userIds)->update(['ccm_status' => Patient::ENROLLED]);
    }

    private function getUsersBasedOnFilters(ParameterBag $params)
    {
        $wpUsers = User::where('program_id', '!=', '')->orderBy('id', 'desc');

        // role filter
        $filterRole = $params->get('filterRole');
        if ( ! empty($filterRole)) {
            if ('all' != $filterRole) {
                $wpUsers->ofType($filterRole);
            }
        }

        // program filter
        $filterProgram = $params->get('filterProgram');
        if ( ! empty($filterProgram)) {
            if ('all' != $filterProgram) {
                $wpUsers->where('program_id', '=', $filterProgram);
            }
        }

        // only let owners see owners
        if ( ! Auth::user()->hasRole(['administrator'])) {
            $wpUsers = $wpUsers->whereHas(
                'roles',
                function ($q) {
                    $q->where('name', '!=', 'administrator');
                }
            );
            // providers can only see their participants
            if (Auth::user()->isProvider()) {
                $wpUsers->whereHas(
                    'roles',
                    function ($q) {
                        $q->whereHas(
                            'perms',
                            function ($q2) {
                                $q2->where('name', '=', 'is-participant');
                            }
                        );
                    }
                );
                $wpUsers->where('program_id', '=', Auth::user()->program_id);
            }
        }

        return $wpUsers->whereIn('id', Auth::user()->viewableUserIds())
            ->select('id')
            ->get();
    }

    private function markPatientsAsUnreachable(array $userIds)
    {
        return Patient::whereIn('user_id', $userIds)->update(['ccm_status' => Patient::UNREACHABLE]);
    }

    private function withdrawUsers($userIds, string $withdrawnReason)
    {
        //need to make sure that we are creating notes for participants
        //and withdrawn patients that are not already withdrawn
        $participantIds = User::ofType('participant')
            ->select('id')
            ->withCount(['inboundCalls'])
            ->whereHas(
                'patientInfo',
                function ($query) {
                    $query->whereNotIn(
                        'ccm_status',
                        [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL]
                    );
                }
            )
            ->whereIn('id', $userIds)
            ->pluck('id', 'inbound_calls_count');

        //See which patients are on first call to update statuses accordingly
        [$withdrawn1stCall, $withdrawn] = $participantIds->partition(
            function ($value, $key) {
                return $key <= 1;
            }
        );

        Patient::whereIn('user_id', $withdrawn)
            ->update(
                [
                    'ccm_status'       => Patient::WITHDRAWN,
                    'withdrawn_reason' => $withdrawnReason,
                    'date_withdrawn'   => Carbon::now()->toDateTimeString(),
                ]
            );

        Patient::whereIn('user_id', $withdrawn1stCall)
            ->update(
                [
                    'ccm_status'       => Patient::WITHDRAWN_1ST_CALL,
                    'withdrawn_reason' => $withdrawnReason,
                    'date_withdrawn'   => Carbon::now()->toDateTimeString(),
                ]
            );

        $authorId = auth()->id();

        $notes = [];
        foreach ($participantIds->all() as $count => $userId) {
            $notes[] = [
                'patient_id'   => $userId,
                'author_id'    => $authorId,
                'logger_id'    => $authorId,
                'body'         => $withdrawnReason,
                'type'         => 'Other',
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
                'performed_at' => Carbon::now(),
            ];
        }

        Note::insert($notes);
    }
}
