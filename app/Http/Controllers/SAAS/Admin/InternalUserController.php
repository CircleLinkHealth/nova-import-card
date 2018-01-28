<?php

namespace App\Http\Controllers\SAAS\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SAAS\StoreInternalUser;
use App\Services\SAAS\Admin\UserManagementService;
use App\User;
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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $data = $this->userManagementService->getDataForCreateUserPage();

        return view('saas.admin.user.create', $data);
    }

    public function store(StoreInternalUser $request) {
        $internalUser = new InternalUser($request['user'], $request['practices'], $request['role']);
        $user = $this->userManagementService->storeInternalUser($internalUser);

        return redirect()->route('saas-admin.users.edit', [
            'userId' => $user->id
        ]);
    }

    public function edit($userId)
    {
        $data = $this->userManagementService->getDataForCreateUserPage();

        $data['editedUser'] = $this->userManagementService->getUser($userId);

        return view('saas.admin.user.create', $data);
    }
}
