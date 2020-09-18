<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\CRUD;

use CircleLinkHealth\CpmAdmin\Http\Requests\CreateSaasAccount;
use CircleLinkHealth\CpmAdmin\Notifications\SAAS\SendInternalUserSignupInvitation;
use CircleLinkHealth\CpmAdmin\Services\SAAS\UserManagementService;
use CircleLinkHealth\CpmAdmin\DTO\InternalUser;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Routing\Controller;

class SaasAccountController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.saasAccounts.create', [
            'submitUrl'    => route('saas-accounts.store'),
            'submitMethod' => 'POST',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSaasAccount $request)
    {
        $saasAccount = SaasAccount::create([
            'name' => $request['name'],
        ]);
        $practice = Practice::updateOrCreate([
            'name'            => $saasAccount->name,
            'display_name'    => $saasAccount->name,
            'saas_account_id' => $saasAccount->id,
        ]);
        $saasAdminRole = Role::whereName('saas-admin')->first();

        $emails = array_map('trim', explode(',', $request['admin_emails']));

        foreach ($emails as $email) {
            $user = [
                'email'                => $email,
                'saas_account_id'      => $saasAccount->id,
                'auto_attach_programs' => 1,
            ];

            $roleId = $saasAdminRole->id;

            $internalUser = new InternalUser($user, $practice->id, $roleId);
            $user         = $this->userManagementService->storeInternalUser($internalUser);
            $user->notify(new SendInternalUserSignupInvitation(auth()->user(), $practice, $saasAccount));
        }

        return "Sent invite to {$request['admin_emails']}";
    }
}
