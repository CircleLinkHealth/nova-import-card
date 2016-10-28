<?php namespace App\Http\Controllers;

use App\CarePlan;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\Repositories\UserRepository;
use App\CPRulesPCP;
use App\Location;
use App\Practice;
use App\Role;
use App\Services\CareplanService;
use App\Services\MsgChooser;
use App\Services\MsgScheduler;
use App\Services\MsgUI;
use App\Services\MsgUser;
use App\Services\ObservationService;
use App\User;
use Auth;
use DateTimeZone;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
        $messages = \Session::get( 'messages' );
        if ( !Auth::user()->can( 'users-view-all' ) ) {
            abort( 403 );
        }
        // TEMPORARY HACK SEED FIX TO REPAIR USER DATA, REMOVE AT LATER DATE
        // START
        $missingProgramId = array();
        $users = User::get();
        foreach ( $users as $user ) {
            // ensure program relationship is set
            /*
            if(!empty($user->program_id) && $user->program_id < 100) {
                if (!$user->programs->contains($user->program_id)) {
                    $user->programs()->attach($user->program_id);
                }
            } else {
                $user->delete();
                $missingProgramId[] = '';
            }
            */
        }
        // END

        if ( $request->header( 'Client' ) == 'ui' ) {
            $userId = Crypt::decrypt( $request->header( 'UserId' ) );

            $wpUser = User::find( $userId );

            return response()->json( Crypt::encrypt( json_encode( $wpUser ) ) );

        }
        else if ( $request->header( 'Client' ) == 'mobi' ) {
            $response = [
                'user' => []
            ];
            $statusCode = 200;

            \JWTAuth::setIdentifier( 'ID' );
            $user = \JWTAuth::parseToken()->authenticate();
            if ( !$user ) {
                return response()->json( ['error' => 'invalid_credentials'], 401 );
            }
            else {
                $userId = $user->ID;
                $wpUser = User::find( $userId );
                $response = [
                    'id' => $wpUser->ID,
                    'user_email' => $wpUser->user_email,
                    'user_registered' => $wpUser->user_registered,
                    'meta' => $wpUser->meta
                ];
                return response()->json( $response, $statusCode );
            }
        }
        else {
            // display view
            $wpUsers = User::where( 'program_id', '!=', '' )->orderBy( 'ID', 'desc' );

            // FILTERS
            $params = $request->all();

            // filter user
            $users = User::whereIn('ID', Auth::user()->viewableUserIds())->OrderBy('id',
                'desc')->get()->pluck('fullNameWithId', 'ID')->all();
            $filterUser = 'all';
            if ( !empty($params[ 'filterUser' ]) ) {
                $filterUser = $params[ 'filterUser' ];
                if ( $params[ 'filterUser' ] != 'all' ) {
                    $wpUsers->where( 'ID', '=', $filterUser );
                }
            }

            // role filter
            $roles = Role::all()->pluck('display_name', 'name')->all();
            $filterRole = 'all';
            if ( !empty($params[ 'filterRole' ]) ) {
                $filterRole = $params[ 'filterRole' ];
                if ( $params[ 'filterRole' ] != 'all' ) {
                    $wpUsers->whereHas( 'roles', function ($q) use ($filterRole) {
                        $q->where( 'name', '=', $filterRole );
                    } );
                }
            }

            // program filter
            $programs = Practice::orderBy('blog_id', 'desc')
                ->whereIn( 'blog_id', Auth::user()->viewableProgramIds() )
                ->get()->pluck('domain', 'blog_id')->all();
            $filterProgram = 'all';
            if ( !empty($params[ 'filterProgram' ]) ) {
                $filterProgram = $params[ 'filterProgram' ];
                if ( $params[ 'filterProgram' ] != 'all' ) {
                    $wpUsers->where( 'program_id', '=', $filterProgram );
                }
            }

            // only let owners see owners
            if ( !Auth::user()->hasRole(['administrator']) ) {
                $wpUsers = $wpUsers->whereHas( 'roles', function ($q) {
                    $q->where( 'name', '!=', 'administrator' );
                } );
                // providers can only see their participants
                if ( Auth::user()->hasRole(['provider']) ) {
                    $wpUsers->whereHas('roles', function ($q) {
                        $q->whereHas('perms', function ($q2) {
                            $q2->where('name', '=', 'is-participant');
                        });
                    });
                    $wpUsers->where( 'program_id', '=', Auth::user()->program_id );
                }
            }

            $queryString = $request->query();

            // patient restriction
            $wpUsers->whereIn( 'ID', Auth::user()->viewableUserIds() );
            $wpUsers = $wpUsers->paginate( 20 );
            $invalidUsers = array();

            return view( 'wpUsers.index', compact( ['messages', 'wpUsers', 'users', 'filterUser', 'programs', 'filterProgram', 'roles', 'filterRole', 'invalidUsers', 'queryString'] ) );
        }

    }


    public function createQuickPatient($programId)
    {
        return $this->quickAddForm( $programId );
    }

    public function quickAddForm($blogId)
    {
        if ( !Auth::user()->can( 'users-create' ) ) {
            abort( 403 );
        }
        //if ( $request->header('Client') == 'ui' ) {}

        $blogItem = Practice::find($blogId)->pcp()->whereStatus('Active')->get();

        foreach ( $blogItem as $item ) {
            // Item Categories
            $sections[] = $item->section_text;
            // Sub Items
            $subItems[ $item->section_text ] = CPRulesPCP::find( $item->pcp_id )->items()->where( 'items_parent', '0' )->where( 'items_text', '!=', '' )->get();
        }//dd($subItems['Diagnosis / Problems to Monitor'][0]->items_id);

        //Array of days
        $weekdays_arr = array('1' => 'Sunday', '2' => 'Monday', '3' => 'Tuesday', '4' => 'Wednesday', '5' => 'Thursday', '6' => 'Friday', '7' => 'Saturday');

        //List of providers
        $provider_raw = Practice::getProviders($blogId);
        $providers = array();
        foreach ( $provider_raw as $provider ) {
            $providers[ $provider->ID ] = $provider->getFullNameAttribute();
        }

        // @todo Check what's the name for Smoking
        // @todo Check how to make the biometrics dynamic
        $biometric_arr = array('Blood Sugar', 'Blood Pressue', 'Smoking (# per day)', 'Weight');
        foreach ( $subItems[ 'Biometrics to Monitor' ] as $key => $value ) {
            if ( !in_array( $value->items_text, $biometric_arr ) ) {
                unset($subItems[ 'Biometrics to Monitor' ][ $key ]);
            }
        }//dd($subItems['Biometrics to Monitor']);

        //List of locations
        $locations = Location::getLocationsForBlog( $blogId );
        //dd($subItems['Biometrics to Monitor']);

        return view( 'wpUsers.quickAdd', ['headings' => $sections, 'items' => $subItems, 'days' => $weekdays_arr, 'providers' => $providers, 'offices' => $locations] );

    }

    public function storeQuickPatient()
    {
        if (!Auth::user()->can('users-create')) {
            abort(403);
        }
        $wpUser = new User;

        // create participant here

        return redirect()->route('admin.users.edit', [$wpUser->ID])->with('messages',
            ['successfully created new user - ' . $wpUser->ID]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if ( !Auth::user()->can( 'users-create' ) ) {
            abort( 403 );
        }
        $messages = \Session::get( 'messages' );

        $wpUser = new User;

        $roles = Role::pluck('name', 'id')->all();

        // user config
        $userConfig = ( new UserConfigTemplate() )->getArray();

        // set role
        $wpRole = '';

        // States (for dropdown)
        $states_arr = array('AL' => "Alabama", 'AK' => "Alaska", 'AZ' => "Arizona", 'AR' => "Arkansas", 'CA' => "California", 'CO' => "Colorado", 'CT' => "Connecticut", 'DE' => "Delaware", 'DC' => "District Of Columbia", 'FL' => "Florida", 'GA' => "Georgia", 'HI' => "Hawaii", 'ID' => "Idaho", 'IL' => "Illinois", 'IN' => "Indiana", 'IA' => "Iowa", 'KS' => "Kansas", 'KY' => "Kentucky", 'LA' => "Louisiana", 'ME' => "Maine", 'MD' => "Maryland", 'MA' => "Massachusetts", 'MI' => "Michigan", 'MN' => "Minnesota", 'MS' => "Mississippi", 'MO' => "Missouri", 'MT' => "Montana", 'NE' => "Nebraska", 'NV' => "Nevada", 'NH' => "New Hampshire", 'NJ' => "New Jersey", 'NM' => "New Mexico", 'NY' => "New York", 'NC' => "North Carolina", 'ND' => "North Dakota", 'OH' => "Ohio", 'OK' => "Oklahoma", 'OR' => "Oregon", 'PA' => "Pennsylvania", 'RI' => "Rhode Island", 'SC' => "South Carolina", 'SD' => "South Dakota", 'TN' => "Tennessee", 'TX' => "Texas", 'UT' => "Utah", 'VT' => "Vermont", 'VA' => "Virginia", 'WA' => "Washington", 'WV' => "West Virginia", 'WI' => "Wisconsin", 'WY' => "Wyoming");

        // programs for dd
        $wpBlogs = Practice::orderBy('blog_id', 'desc')->pluck('domain', 'blog_id')->all();

        $locations = Location::whereNotNull('parent_id')->pluck('name', 'id')->all();

        // timezones for dd
        $timezones_raw = DateTimeZone::listIdentifiers( DateTimeZone::ALL );
        foreach ( $timezones_raw as $timezone ) {
            $timezones_arr[ $timezone ] = $timezone;
        }

        // providers
        $providers_arr = array('provider' => 'provider', 'office_admin' => 'office_admin', 'participant' => 'participant', 'care_center' => 'care_center', 'viewer' => 'viewer', 'clh_participant' => 'clh_participant', 'clh_administrator' => 'clh_administrator');

        // display view
        return view( 'wpUsers.create', [
            'wpUser' => $wpUser,
            'states_arr' => $states_arr,
            'timezones_arr' => $timezones_arr,
            'wpBlogs' => $wpBlogs,
            'userConfig' => $userConfig,
            'providers_arr' => $providers_arr,
            'messages' => $messages,
            'roles' => $roles,
            'locations' => $locations,
        ] );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if ( !Auth::user()->can( 'users-create' ) ) {
            abort( 403 );
        }
        $params = new ParameterBag( $request->input() );

        $userRepo = new UserRepository();

        $wpUser = new User;

        $this->validate( $request, $wpUser->rules );

        $wpUser = $userRepo->createNewUser( $wpUser, $params );

        //if location was selected save it
        if ( is_numeric( $locationId = $request->input( 'location_id' ) ) ) {
            $wpUser->locations()->attach( Location::find( $locationId ) );
        }


        return redirect()->route( 'admin.users.edit', [$wpUser->ID] )->with( 'messages', ['successfully created new user - ' . $wpUser->ID] );

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        if ( !Auth::user()->can( 'users-view-all' ) ) {
            abort( 403 );
        }
        dd( 'user /edit to view user info' );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        if ( !Auth::user()->can( 'users-edit-all' ) ) {
            abort( 403 );
        }
        $messages = \Session::get( 'messages' );

        $patient = User::find( $id );
        if ( !$patient ) {
            return response( "User not found", 401 );
        }

        $roles = Role::pluck('name', 'id')->all();
        $role = $patient->roles()->first();
        if ( !$role ) {
            $role = Role::first();
        }

        // build revision info
        $revisions = array();

        // first for user
        $revisionHistory = collect( [] );
        foreach ( $patient->revisionHistory->sortByDesc( 'updated_at' )->take( 10 ) as $history ) {
            $revisionHistory->push( $history );
        }
        $revisions['User'] = $revisionHistory;

        // patientInfo
        if($role->name == 'participant') {
            $revisionHistory = collect([]);
            foreach ($patient->patientInfo->revisionHistory->sortByDesc('updated_at')->take(10) as $history) {
                $revisionHistory->push($history);
            }
            $revisions['Patient Info'] = $revisionHistory;
        }

        $params = $request->all();
        if ( !empty($params) ) {
            if ( isset($params[ 'action' ]) ) {
                if ( $params[ 'action' ] == 'impersonate' ) {
                    Auth::login( $id );
                    return redirect()->route( '/', [] )->with( 'messages', ['Logged in as user ' . $id] );
                }
            }
        }

        // locations @todo get location id for Practice
        $wpBlog = Practice::find($patient->program_id);
        $locations_arr = array();
        if ( $wpBlog ) {
            $locations_arr = ( new Location )->getNonRootLocations( $wpBlog->locationId() );
        }

        $carePlans = CarePlan::where('program_id', '=', $patient->program_id)->pluck('display_name', 'id')->all();

        // States (for dropdown)
        $states_arr = array('AL' => "Alabama", 'AK' => "Alaska", 'AZ' => "Arizona", 'AR' => "Arkansas", 'CA' => "California", 'CO' => "Colorado", 'CT' => "Connecticut", 'DE' => "Delaware", 'DC' => "District Of Columbia", 'FL' => "Florida", 'GA' => "Georgia", 'HI' => "Hawaii", 'ID' => "Idaho", 'IL' => "Illinois", 'IN' => "Indiana", 'IA' => "Iowa", 'KS' => "Kansas", 'KY' => "Kentucky", 'LA' => "Louisiana", 'ME' => "Maine", 'MD' => "Maryland", 'MA' => "Massachusetts", 'MI' => "Michigan", 'MN' => "Minnesota", 'MS' => "Mississippi", 'MO' => "Missouri", 'MT' => "Montana", 'NE' => "Nebraska", 'NV' => "Nevada", 'NH' => "New Hampshire", 'NJ' => "New Jersey", 'NM' => "New Mexico", 'NY' => "New York", 'NC' => "North Carolina", 'ND' => "North Dakota", 'OH' => "Ohio", 'OK' => "Oklahoma", 'OR' => "Oregon", 'PA' => "Pennsylvania", 'RI' => "Rhode Island", 'SC' => "South Carolina", 'SD' => "South Dakota", 'TN' => "Tennessee", 'TX' => "Texas", 'UT' => "Utah", 'VT' => "Vermont", 'VA' => "Virginia", 'WA' => "Washington", 'WV' => "West Virginia", 'WI' => "Wisconsin", 'WY' => "Wyoming");

        // programs for dd
        $wpBlogs = Practice::orderBy('blog_id', 'desc')->pluck('domain', 'blog_id')->all();

        // timezones for dd
        $timezones_raw = DateTimeZone::listIdentifiers( DateTimeZone::ALL );
        foreach ( $timezones_raw as $timezone ) {
            $timezones_arr[ $timezone ] = $timezone;
        }

        // providers
        $providers_arr = array('provider' => 'provider', 'office_admin' => 'office_admin', 'participant' => 'participant', 'care_center' => 'care_center', 'viewer' => 'viewer', 'clh_participant' => 'clh_participant', 'clh_administrator' => 'clh_administrator');

        // display view
        return view( 'wpUsers.edit', ['patient' => $patient, 'locations_arr' => $locations_arr, 'states_arr' => $states_arr, 'timezones_arr' => $timezones_arr, 'wpBlogs' => $wpBlogs, 'primaryBlog' => $patient->program_id, 'providers_arr' => $providers_arr, 'messages' => $messages, 'role' => $role, 'roles' => $roles, 'revisions' => $revisions, 'carePlans' => $carePlans] );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if ( !Auth::user()->can( 'users-edit-all' ) ) {
            abort( 403 );
        }
        // instantiate user
        $wpUser = User::find( $id );
        if ( !$wpUser ) {
            return response( "User not found", 401 );
        }
        
        // input
        $params = new ParameterBag( $request->input() );

        $userRepo = new UserRepository();

        $userRepo->editUser( $wpUser, $params );

        return redirect()->back()->with( 'messages', ['successfully updated user'] );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     *
     */
    public function destroy($id)
    {
        if ( !Auth::user()->can( 'users-edit-all' ) ) {
            abort( 403 );
        }

        $user = User::find( $id );
        if ( !$user ) {
            return response( "User not found", 401 );
        }

        //$user->programs()->detach();
        $user->delete();

        return redirect()->back()->with( 'messages', ['successfully deleted user'] );
    }


    /**
     * Scramble user(s)
     *
     * @return Response
     *
     */
    public function doAction(Request $request)
    {
        if ( !Auth::user()->can( 'users-edit-all' ) ) {
            abort( 403 );
        }

        // input
        $params = new ParameterBag( $request->input() );

        if ( $params->get( 'action' ) && $params->get( 'action' ) == 'scramble' ) {
            if ( $params->get( 'users' ) && !empty($params->get( 'users' )) ) {
                $this->scrambleUsers( $params->get( 'users' ) );
                return redirect()->back()->with( 'messages', ['successfully scrambled users'] );
            }
        }

        return redirect()->back();
    }

    /**
     * Scramble user(s)
     *
     * @return Response
     *
     */
    public function scrambleUsers($userIds)
    {
        foreach ( $userIds as $id ) {
            $user = User::find( $id );
            if ( !$user ) {
                return false;
            }

            $user->scramble();
            /*

            $user->mobile_phone_number = $randomUserInfo->email;
            $user->agent_name = $randomUserInfo->email;
            $user->agent_telephone = $randomUserInfo->email;
            $user->agent_email = $randomUserInfo->email;
            $user->agent_relationship = $randomUserInfo->email;
            $user->agent_relationship = $randomUserInfo->email;
            */

            /*
            // echo "<pre>"; var_export($parsed_json);echo "</pre>";
            $z = 0;
            foreach ($scramble_me as $key) {
                $user_meta = get_user_meta($key);//, 'wp_'.$blog_id.'_capabilities', true);
                $user_config_meta = get_user_meta($key, 'wp_' . $blog_id . '_user_config', true);
                $ret = update_user_meta($key, 'first_name', ucfirst($parsed_json->{'results'}[$z]->user->name->first));
                $ret = update_user_meta($key, 'last_name', ucfirst("z" . $parsed_json->{'results'}[$z]->user->name->last));
                $user_config_meta['mrn_number'] = uniqid();
                $user_config_meta['gender'] = ($parsed_json->{'results'}[$z]->user->gender) == 'male' ? 'M' : 'F';
                $user_config_meta['email'] = $parsed_json->{'results'}[$z]->user->email;
                $user_config_meta['address'] = $parsed_json->{'results'}[$z]->user->location->street;
                $user_config_meta['address2'] = "Garage";
                $user_config_meta['city'] = $parsed_json->{'results'}[$z]->user->location->city;

                $user_config_meta['state'] = $states[ucfirst($parsed_json->{'results'}[$z]->user->location->state)];
                $user_config_meta['zip'] = "" . $parsed_json->{'results'}[$z]->user->location->zip . "";
                $user_config_meta['birth_date'] = date("Y-m-d H:i:s 0500", $parsed_json->{'results'}[$z]->user->dob / 1000);
                $user_config_meta['study_phone_number'] = $parsed_json->{'results'}[$z]->user->phone;
                $user_config_meta['mobile_phone_number'] = $parsed_json->{'results'}[$z]->user->cell;
                $user_config_meta['study_phone_number'] = str_replace(array("(", ")"), "", $user_config_meta['study_phone_number']);
                $user_config_meta['mobile_phone_number'] = str_replace(array("(", ")"), "", $user_config_meta['mobile_phone_number']);
                $user_config_meta['agent-name'] = "Dad";
                $user_config_meta['agent-telephone'] = $user_config_meta['study_phone_number'];
                $user_config_meta['agent-email'] = "Dad@example.com";
                $user_config_meta['agent-relationship'] = "Father";
                $ret = wp_update_user(array('ID' => $key,
                    'user_nicename' => uniqid(),
                    'user_email' => $user_config_meta['email'],
                    'display_name' => uniqid(),
                    'user_pass' => uniqid()
                ));
                // var_dump($user_meta);
                // var_dump($user_config_meta);
                $ret = update_user_meta($key, 'wp_' . $blog_id . '_user_config', $user_config_meta);

                $z++;
            }
            */
        }
        return true;
    }


    public function getPatients()
    {
        return User::all();
    }


    public function showQuickAddAPI()
    {
        // render quick add view
        $viewHtml = '<html><h1>Header</h1><p>Paragraph</p></html>';

        // return view html
        return response( $viewHtml );
    }

    public function storeQuickAddAPI(Request $request)
    {
        //return $request;

//		if ( $request->header('Client') == 'ui' ) { // WP Site
//			$params = json_decode(Crypt::decrypt($request->input('data')), true);
//		} else {
//			$params = $request->all();
//		}
//
//		$params = new ParameterBag($params);
//
//		$userRepo = new UserRepository();
//
//		$wpUser = new User;
//
//		$this->validate($request, $wpUser->rules);
//
//		$wpUser = $userRepo->createNewUser($wpUser, $params);
//
//		// render quick add view
//		$viewHtml = '<html><h1>Header</h1><p>Paragraph</p></html>';
//
//		// return view html
//		return response($viewHtml);
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function showMsgCenter(Request $request, $id)
    {
        if ( !Auth::user()->can( 'users-view-all' ) ) {
            abort( 403 );
        }
        $msgUI = new MsgUI;
        $msgUsers = new MsgUser;
        $msgChooser = new MsgChooser;
        $msgScheduler = new MsgScheduler;
        $observationService = new ObservationService;
        //dd($result);
        $wpUser = User::find( $id );
        if ( !$wpUser ) {
            return response( "User not found", 401 );
        }
        $params = $request->input();
        $userMeta = $wpUser->userMeta();

        $messageKey = '';
        $messageValue = '';
        $activeDate = ''; // keeps date section open
        if ( !empty($params) ) {
            if ( isset($params[ 'action' ]) ) {
                if ( $params[ 'action' ] == 'sendTextSimulation' ) {
                    // deprecated
                }
                else if ( $params[ 'action' ] == 'run_scheduler' ) {
                    $result = $msgScheduler->index( $wpUser->blogId() );
                    return response()->json( $result );
                }
                else if ( $params[ 'action' ] == 'save_app_obs' ) {
                    $result = $observationService->storeObservationFromApp( $id, $params[ 'parent_id' ], $params[ 'obs_value' ], $params[ 'obs_date' ], $params[ 'msg_id' ], $params[ 'obs_key' ], 'America/New_York' );
                    // create message
                    if ( $result ) {
                        $messageKey = 'success';
                        $messageValue = 'Successfully saved new app observation.';
                    }
                    else {
                        $messageKey = 'error';
                        $messageValue = 'Failed to save app observation.';
                    }
                    // add param to keep date section open
                    $date = strtotime( $params[ 'obs_date' ] );
                    $activeDate = date( 'Y-m-d', $date );
                }
            }
        }

        $commentsForUser = $msgUsers->get_comments_for_user( $wpUser->ID, $wpUser->blogId() );
        $comments = array();
        if ( !empty($commentsForUser) ) {
            foreach ( $commentsForUser as $comment ) {
                $comments[ $comment->comment_ID ] = array(
                    'comment_type' => $comment->comment_type,
                    'comment_author' => $comment->comment_author,
                    'comment_date' => $comment->comment_date,
                    'comment_approved' => $comment->comment_approved,
                    'comment_parent' => $comment->comment_parent,
                    'comment_content' => $comment->comment_content,
                    //'comment_content_array' => unserialize($comment->comment_content),
                    'comment_content_array' => $comment->comment_content,
                );
            }
        }

        // get dates
        $date1 = date( 'Y-m-d' );
        $date2 = date( 'Y-m-d', time() - 60 * 60 * 24 );
        $date3 = date( 'Y-m-d', time() - ((60 * 60 * 24) * 2) );
        $dates = array($date1, $date2, $date3);
        if ( empty($dates) ) {
            return response( "Date array is required", 401 );
        }

        // get feed
        $careplanService = new CareplanService;
        $cpFeed = $careplanService->getCareplan( $wpUser, $dates );
        //$cpFeed = json_decode(file_get_contents(getenv('CAREPLAN_JSON_PATH')), 1);
        $cpFeed = $msgUI->addAppSimCodeToCP( $cpFeed );
        $cpFeedSections = array('Biometric', 'DMS', 'Symptoms', 'Reminders');

        //return response()->json($cpFeed);
        return view( 'wpUsers.msgCenter', ['wpUser' => $wpUser, 'userMeta' => $userMeta, 'cpFeed' => $cpFeed, 'cpFeedSections' => $cpFeedSections, 'comments' => $comments, 'messages' => array(), $messageKey => $messageValue, 'activeDate' => $activeDate] );
    }

}
