<?php

namespace App\Http\Controllers\SAAS\Admin;

use App\Services\Admin\UserManagementService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InternalUserController extends Controller
{
    /**
     * @var UserManagementService
     */
    private $userManagementService;

    public function __construct(UserManagementService $userManagementService)
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
}
