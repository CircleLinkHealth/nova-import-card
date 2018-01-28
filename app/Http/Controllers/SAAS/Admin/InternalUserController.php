<?php

namespace App\Http\Controllers\SAAS\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SAAS\StoreInternalUser;
use App\ValueObjects\SAAS\Admin\InternalUser;

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
        $internalUser = new InternalUser($request['user'], $request['practices'], $request['role']);
        $user         = $this->userManagementService->storeInternalUser($internalUser);

        return redirect()->route('saas-admin.users.edit', [
            'userId' => $user->id,
        ])->with('messages', 'User created successfully!');
    }
}
