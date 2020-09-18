<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\SAAS\Admin\CRUD;

use App\Http\Controllers\Controller;
use App\Http\Requests\SAAS\StoreInternalUser;
use App\Notifications\SAAS\SendInternalUserSignupInvitation;
use CircleLinkHealth\CpmAdmin\DTO\InternalUser;
use Auth;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\ParameterBag;

class InternalUserController extends Controller
{
    /**
     * @var \CircleLinkHealth\CpmAdmin\Services\SAAS\UserManagementService
     */
    private $userManagementService;

    public function __construct(\CircleLinkHealth\CpmAdmin\Services\SAAS\UserManagementService $userManagementService)
    {
        $this->userManagementService = $userManagementService;
    }

    public function action(Request $request)
    {
        $params = new ParameterBag($request->input());

        if ($params->get('action') && 'delete' == $params->get('action')) {
            foreach ($params->get('users') as $userId) {
                User::whereId($userId)
                    ->delete();
            }

            return redirect()->back()->with('messages', ['successfully scrambled users']);
        }

        return redirect()->back();
    }

    /**
     * Show the page to create a new internal user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $data                 = $this->userManagementService->getDataForCreateUserPage();
        $data['submitUrl']    = route('saas-admin.users.store');
        $data['submitMethod'] = 'post';
        $data['titleVerb']    = 'Add';

        return view('saas.admin.user.manage', $data);
    }

    /**
     * Show the page to edit an internal user.
     *
     * @param $userId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($userId)
    {
        $data = $this->userManagementService->getDataForCreateUserPage();

        $data['editedUser'] = $this->userManagementService->getUser($userId);

        $data['submitUrl']      = route('saas-admin.users.update', ['userId' => $userId]);
        $data['submitMethod']   = 'patch';
        $data['titleVerb']      = 'Edit';
        $data['successMessage'] = Session::get('messages');

        return view('saas.admin.user.manage', $data);
    }

    public function index(Request $request)
    {
        $practiceIds = auth()->user()->viewableProgramIds();

        $wpUsers = User::whereHas('practices', function ($q) use ($practiceIds) {
            $q->whereIn('id', $practiceIds);
        })->orderBy('id', 'desc');

        // FILTERS
        $params = $request->all();

        // filter user
        $users = User::whereHas('practices', function ($q) use ($practiceIds) {
            $q->whereIn('id', $practiceIds);
        })
            ->orderBy('display_name')
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
            if ('all' != $params['filterUser']) {
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

        $authIsAdmin = auth()->user()->isAdmin();

        if ($authIsAdmin) {
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
            if ('all' != $params['filterRole']) {
                $wpUsers->ofType($filterRole);
            }
        }

        // program filter
        $programs = Practice::whereIn('id', $practiceIds)
            ->orderBy('display_name')
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
        if ( ! $authIsAdmin) {
            $wpUsers = $wpUsers->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'administrator');
            });
            // providers can only see their participants
            if (Auth::user()->isProvider()) {
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

    /**
     * Store an internal user.
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
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

    public function update(StoreInternalUser $request, $userId)
    {
        $userAttr = $request['user'];

        if ( ! $request->has('user.auto_attach_programs')) {
            $userAttr['auto_attach_programs'] = false;
        }

        $internalUser = new InternalUser($userAttr, $request['practices'], $request['role']);
        $user         = $this->userManagementService->storeInternalUser($internalUser);

        return redirect()->route('saas-admin.users.edit', [
            'userId' => $user->id,
        ])->with('messages', 'User created successfully!');
    }
}
