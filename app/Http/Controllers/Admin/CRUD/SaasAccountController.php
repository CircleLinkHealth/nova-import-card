<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin\CRUD;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSaasAccount;
use App\Notifications\SAAS\SendInternalUserSignupInvitation;
use App\Services\SAAS\Admin\UserManagementService;
use App\ValueObjects\SAAS\Admin\InternalUser;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Http\Request;

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
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }
}
