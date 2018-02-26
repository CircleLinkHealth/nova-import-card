<?php

namespace App\Http\Controllers\SAAS\Admin\CRUD;

use App\Http\Controllers\Controller;
use App\Http\Requests\SAAS\StoreInternalUser;
use App\Notifications\SAAS\SendInternalUserSignupInvitation;
use App\Practice;
use App\Role;
use App\User;
use App\ValueObjects\SAAS\Admin\InternalUser;
use Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class InternalUserController extends Controller
{
    /**
     * @var \App\Services\SAAS\Admin\UserManagementService
     */
    private $userManagementService;

    public function __construct(\App\Services\SAAS\Admin\UserManagementService $userManagementService)
    {
        $this->userManagementService = $userManagementService;
    }

    /**
     * Show the page to create a new internal user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $data = $this->userManagementService->getDataForCreateUserPage();

        $data['submitUrl']    = route('saas-admin.users.store');
        $data['submitMethod'] = 'post';

        return view('saas.admin.user.manage', $data);
    }

    /**
     * Store an internal user
     *
     * @param StoreInternalUser $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function store(StoreInternalUser $request)
    {
        $internalUser = new InternalUser($request['user'], $request['practices'], $request['role']);
        $user         = $this->userManagementService->storeInternalUser($internalUser);

        $practices = Practice::whereIn('id', $internalUser->getPractices())
                             ->get();

        $user->notify(new SendInternalUserSignupInvitation(auth()->user(), $practices, auth()->user()->saasAccount));

        return redirect()->route('saas-admin.users.edit', [
            'userId' => $user->id,
        ])->with('messages', 'User created successfully!');
    }

    /**
     * Show the page to edit an internal user
     *
     * @param $userId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($userId)
    {
        $data = $this->userManagementService->getDataForCreateUserPage();

        $data['editedUser'] = $this->userManagementService->getUser($userId);

        $data['submitUrl']    = route('saas-admin.users.update', ['userId' => $userId]);
        $data['submitMethod'] = 'patch';

        return view('saas.admin.user.manage', $data);
    }

    public function update(StoreInternalUser $request, $userId)
    {
        $userAttr = $request['user'];

        if (!$request->has('user.auto_attach_programs')) {
            $userAttr['auto_attach_programs'] = false;
        }

        $internalUser = new InternalUser($userAttr, $request['practices'], $request['role']);
        $user         = $this->userManagementService->storeInternalUser($internalUser);

        return redirect()->route('saas-admin.users.edit', [
            'userId' => $user->id,
        ])->with('messages', 'User created successfully!');
    }

    public function index(Request $request)
    {
        $practices = auth()->user()->practices;

        $wpUsers = User::whereHas('practices', function ($q) use ($practices) {
            $q->whereIn('id', $practices->pluck('id')->all());
        })->orderBy('id', 'desc');

        // FILTERS
        $params = $request->all();

        // filter user
        $users = User::whereIn('id', Auth::user()->viewableUserIds())
                     ->orderBy('display_name')
                     ->get()
                     ->mapWithKeys(function ($user) {
                         return [
                             $user->id => "{$user->first_name} {$user->last_name} ({$user->id})",
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

        $rolesArray = [
            'care-center',
            'med_assistant',
            'provider',
            'participant',
            'office_admin',
            'saas-admin',
            'specialist',
            'registered-nurse',
        ];

        if (auth()->user()->hasRole('administrator')) {
            $rolesArray[] = 'administrator';
        }

        // role filter
        $roles = Role::whereIn('name', $rolesArray)
                     ->orderBy('display_name')
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
        $programs = Practice::whereIn('id', Auth::user()->viewableProgramIds())
                            ->orderBy('display_name')
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
                $wpUsers->ofType('participant');
            }
        }

        $queryString = $request->query();

        // patient restriction
        $wpUsers->whereIn('id', Auth::user()->viewableUserIds());
        $wpUsers      = $wpUsers->paginate(20);
        $invalidUsers = [];

        return view('saas.admin.user.index', compact([
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

    public function action(Request $request)
    {
        $params = new ParameterBag($request->input());

        if ($params->get('action') && $params->get('action') == 'delete') {
            foreach ($params->get('users') as $userId) {
                User::whereId($userId)
                    ->delete();
            }

            return redirect()->back()->with('messages', ['successfully scrambled users']);
        }

        return redirect()->back();
    }
}