<?php

namespace App\Http\Controllers\Admin\CRUD;

use App\Http\Requests\CreateSaasAccount;
use App\Notifications\SAAS\SendInternalUserSignupInvitation;
use App\Practice;
use App\Role;
use App\SaasAccount;
use App\Services\SAAS\Admin\UserManagementService;
use App\ValueObjects\SAAS\Admin\InternalUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSaasAccount $request)
    {
        $saasAccount   = SaasAccount::create([
            'name' => $request['name'],
        ]);
        $practice      = Practice::updateOrCreate([
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
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
