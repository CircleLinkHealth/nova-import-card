<?php namespace App\Http\Controllers;

use App\CLH\Repositories\UserRepository;
use App\CPRulesPCP;
use App\Location;
use App\Note;
use App\Patient;
use App\Practice;
use App\Role;
use App\User;
use Auth;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $messages = \Session::get('messages');

        $missingProgramId = [];
        $users            = User::all();

        // display view
        $wpUsers = User::where('program_id', '!=', '')->orderBy('id', 'desc');

        // FILTERS
        $params = $request->all();

        // filter user
        $users = User::whereIn('id', Auth::user()->viewableUserIds())
                     ->orderBy('id', 'desc')
                     ->get()
                     ->mapWithKeys(function ($user) {
                         return [
                             $user->id => "{$user->getFirstName()} {$user->getLastName()} ({$user->id})",
                         ];
                     })
                     ->all();

        $filterUser = 'all';

        if ( ! empty($params['filterUser'])) {
            $filterUser = $params['filterUser'];
            if ($params['filterUser'] != 'all') {
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
            if ($params['filterRole'] != 'all') {
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
            if ($params['filterProgram'] != 'all') {
                $wpUsers->where('program_id', '=', $filterProgram);
            }
        }

        // only let owners see owners
        if ( ! Auth::user()->hasRole(['administrator'])) {
            $wpUsers = $wpUsers->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'administrator');
            });
            // providers can only see their participants
            if (Auth::user()->hasRole(['provider'])) {
                $wpUsers->whereHas('roles', function ($q) {
                    $q->whereHas('perms', function ($q2) {
                        $q2->where('name', '=', 'is-participant');
                    });
                });
                $wpUsers->where('program_id', '=', Auth::user()->program_id);
            }
        }

        $queryString = $request->query();

        // patient restriction
        $wpUsers->whereIn('id', Auth::user()->viewableUserIds());
        $wpUsers      = $wpUsers->paginate(20);
        $invalidUsers = [];

        return view('wpUsers.index', compact([
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
        ]));
    }


    public function createQuickPatient($programId)
    {
        return $this->quickAddForm($programId);
    }

    public function quickAddForm($blogId)
    {
        if ( ! Auth::user()->isAdmin()) {
            abort(403);
        }
        //if ( $request->header('Client') == 'ui' ) {}

        $blogItem = Practice::find($blogId)->pcp()->whereStatus('Active')->get();

        foreach ($blogItem as $item) {
            // Item Categories
            $sections[] = $item->section_text;
            // Sub Items
            $subItems[$item->section_text] = CPRulesPCP::find($item->pcp_id)->items()->where(
                'items_parent',
                '0'
            )->where('items_text', '!=', '')->get();
        }//dd($subItems['Diagnosis / Problems to Monitor'][0]->items_id);

        //Array of days
        $weekdays_arr = [
            '1' => 'Sunday',
            '2' => 'Monday',
            '3' => 'Tuesday',
            '4' => 'Wednesday',
            '5' => 'Thursday',
            '6' => 'Friday',
            '7' => 'Saturday',
        ];

        //List of providers
        $provider_raw = Practice::getProviders($blogId);
        $providers    = [];
        foreach ($provider_raw as $provider) {
            $providers[$provider->id] = $provider->getFullName();
        }

        // @todo Check what's the name for Smoking
        // @todo Check how to make the biometrics dynamic
        $biometric_arr = [
            'Blood Sugar',
            'Blood Pressue',
            'Smoking (# per day)',
            'Weight',
        ];
        foreach ($subItems['Biometrics to Monitor'] as $key => $value) {
            if ( ! in_array($value->items_text, $biometric_arr)) {
                unset($subItems['Biometrics to Monitor'][$key]);
            }
        }//dd($subItems['Biometrics to Monitor']);

        //List of locations
        $locations = Location::getLocationsForBlog($blogId);

        //dd($subItems['Biometrics to Monitor']);

        return view('wpUsers.quickAdd', [
            'headings'  => $sections,
            'items'     => $subItems,
            'days'      => $weekdays_arr,
            'providers' => $providers,
            'offices'   => $locations,
        ]);
    }

    public function storeQuickPatient()
    {
        if ( ! Auth::user()->isAdmin()) {
            abort(403);
        }
        $wpUser = new User;

        // create participant here

        return redirect()->route('admin.users.edit', [$wpUser->id])->with(
            'messages',
            ['successfully created new user - ' . $wpUser->id]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $messages = \Session::get('messages');

        $wpUser = new User;

        $roles = Role::pluck('display_name', 'id')->all();

        // set role
        $wpRole = '';

        // States (for dropdown)
        $states_arr = [
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
        return view('wpUsers.create', [
            'wpUser'        => $wpUser,
            'states_arr'    => $states_arr,
            'timezones_arr' => $timezones_arr,
            'wpBlogs'       => $wpBlogs,
            'providers_arr' => $providers_arr,
            'messages'      => $messages,
            'roles'         => $roles,
            'locations'     => $locations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params = new ParameterBag($request->input());

        $userRepo = new UserRepository();

        $wpUser = new User;

        $this->validate($request, $wpUser->rules);

        $wpUser = $userRepo->createNewUser($wpUser, $params);

        if ($request->has('provider_id')) {
            $wpUser->setBillingProviderId($request->input('provider_id'));
        }

        //if location was selected save it
        if (is_numeric($locationId = $request->input('location_id'))) {
            $wpUser->locations()->attach(Location::find($locationId));
        }


        return redirect()->route('admin.users.edit', [$wpUser->id])->with(
            'messages',
            ['successfully created new user - ' . $wpUser->id]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
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
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit(
        Request $request,
        $id
    ) {
        $messages = \Session::get('messages');

        $patient = User::find($id);
        if ( ! $patient) {
            return response("User not found", 401);
        }

        $roles = Role::pluck('name', 'id')->all();
        $role  = $patient->roles()->first();
        if ( ! $role) {
            $role = Role::first();
        }

        // build revision info
        $revisions = [];

        // first for user
        $revisionHistory = collect([]);
        foreach ($patient->revisionHistory->sortByDesc('updated_at')->take(10) as $history) {
            $revisionHistory->push($history);
        }
        $revisions['User'] = $revisionHistory;

        // patientInfo
        if ($role->name == 'participant') {
            $revisionHistory = collect([]);

            if ($patient->patientInfo) {
                foreach ($patient->patientInfo->revisionHistory->sortByDesc('updated_at')->take(10) as $history) {
                    $revisionHistory->push($history);
                }
            }

            $revisions['Patient Info'] = $revisionHistory;
        }

        $params = $request->all();
        if ( ! empty($params)) {
            if (isset($params['action'])) {
                if ($params['action'] == 'impersonate') {
                    Auth::login($id);

                    return redirect()->route('/', [])->with('messages', ['Logged in as user ' . $id]);
                }
            }
        }

        // locations @todo get location id for Practice
        $practice      = Practice::find($patient->program_id);
        $locations_arr = [];
        if ($practice) {
            $locations_arr = $practice->locations->all();
        }

        // States (for dropdown)
        $states_arr = [
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
        return view('wpUsers.edit', [
            'patient'       => $patient,
            'locations_arr' => $locations_arr,
            'states_arr'    => $states_arr,
            'timezones_arr' => $timezones_arr,
            'wpBlogs'       => $wpBlogs,
            'primaryBlog'   => $patient->program_id,
            'providers_arr' => $providers_arr,
            'messages'      => $messages,
            'role'          => $role,
            'roles'         => $roles,
            'revisions'     => $revisions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update(
        Request $request,
        $id
    ) {
        $wpUser = User::find($id);
        if ( ! $wpUser) {
            return response("User not found", 401);
        }

        // input
        $params = new ParameterBag($request->input());

        $userRepo = new UserRepository();

        $userRepo->editUser($wpUser, $params);

        if ($request->has('provider_id')) {
            $wpUser->setBillingProviderid($request->input('provider_id'));
        }

        return redirect()->back()->with('messages', ['successfully updated user']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     *
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if ( ! $user) {
            return response("User not found", 401);
        }

        //$user->practices()->detach();
        $user->delete();

        return redirect()->back()->with('messages', ['successfully deleted user']);
    }


    /**
     * Perform actions on multiple users
     *
     * @return Response
     *
     */
    public function doAction(Request $request)
    {
        $params = new ParameterBag($request->input());

        $action = $params->get('action');

        if ( ! $action) {
            return redirect()->back()->withErrors(["form_error" => "There was an error: Missing 'action' parameter."]);
        }

        if ($action == 'scramble' || $action == 'withdraw') {

            $selectAllFromFilters = ! empty($params->get('filterRole')) || ! empty($params->get('filterProgram'));
            if ($selectAllFromFilters) {
                $users = $this->getUsersBasedOnFilters($params);
            } else {
                $users = $params->get('users');
            }

            if (empty($users)) {
                return redirect()->back()->withErrors(["form_error" => "There was an error: Users array is empty."]);
            }

            if ($action == 'scramble') {
                $this->scrambleUsers($users);
                return redirect()->back()->with('messages', ['Action [Scramble] was successful']);
            } else if ($action == 'withdraw') {
                $this->withdrawUsers($users, $params->get('withdrawal-note-body'));
                return redirect()->back()->with('messages', ['Action [Withdraw] was successful']);
            }
        } else {
            return redirect()->back()->withErrors(["form_error" => "Unhandled action: $action"]);
        }

        return redirect()->back();
    }

    /**
     * Scramble user(s)
     *
     * @return boolean
     *
     */
    public function scrambleUsers($userIds)
    {
        foreach ($userIds as $id) {
            $user = User::find($id);
            if ( ! $user) {
                return false;
            }

            $user->scramble();
        }

        return true;
    }

    private function withdrawUsers($userIds, String $noteBody)
    {
        //need to make sure that we are creating notes for participants
        //and withdrawn patients that are not already withdrawn
        $participantIds = User::ofType('participant')
                              ->whereHas('patientInfo', function ($query) {
                                  $query->where('ccm_status', '!=', 'withdrawn');
                              })
                              ->whereIn('id', $userIds)
                              ->select(['id'])
                              ->pluck('id')
                              ->all();

        Patient::whereIn('user_id', $participantIds)
               ->update([
                   'ccm_status'     => 'withdrawn',
                   'date_withdrawn' => Carbon::now()->toDateTimeString(),
               ]);

        $authorId = auth()->id();

        $notes = [];
        foreach ($participantIds as $userId) {
            $notes[] = [
                'patient_id'   => $userId,
                'author_id'    => $authorId,
                'logger_id'    => $authorId,
                'body'         => $noteBody,
                'type'         => 'Other',
                'performed_at' => Carbon::now(),
            ];
        }

        Note::insert($notes);
    }

    private function getUsersBasedOnFilters(ParameterBag $params)
    {

        $wpUsers = User::where('program_id', '!=', '')->orderBy('id', 'desc');

        // role filter
        $filterRole = $params->get('filterRole');
        if ( ! empty($filterRole)) {
            if ($filterRole != 'all') {
                $wpUsers->ofType($filterRole);
            }
        }

        // program filter
        $filterProgram = $params->get('filterProgram');
        if ( ! empty($filterProgram)) {
            if ($filterProgram != 'all') {
                $wpUsers->where('program_id', '=', $filterProgram);
            }
        }

        // only let owners see owners
        if ( ! Auth::user()->hasRole(['administrator'])) {
            $wpUsers = $wpUsers->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'administrator');
            });
            // providers can only see their participants
            if (Auth::user()->hasRole(['provider'])) {
                $wpUsers->whereHas('roles', function ($q) {
                    $q->whereHas('perms', function ($q2) {
                        $q2->where('name', '=', 'is-participant');
                    });
                });
                $wpUsers->where('program_id', '=', Auth::user()->program_id);
            }
        }

        return $wpUsers->whereIn('id', Auth::user()->viewableUserIds())
                       ->select('id')
                       ->get();
    }


    public function getPatients()
    {
        return User::all();
    }
}
