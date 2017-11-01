<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePracticeStaff;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\Role;
use App\User;
use Illuminate\Support\Collection;

class PracticeStaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($primaryPracticeId)
    {
        $primaryPractice = Practice::find($primaryPracticeId);

        $relevantRoles = [
            'med_assistant',
            'office_admin',
            'provider',
            'registered-nurse',
            'specialist',
        ];

        $practiceUsers = User::ofType(array_merge($relevantRoles, ['practice-lead']))
            ->whereHas('practices', function ($q) use (
                $primaryPractice
            ) {
                $q->where('id', '=', $primaryPractice->id);
            })
            ->with('roles')
            ->get()
            ->sortBy('first_name')
            ->values();

        if (!auth()->user()->hasRole('administrator')) {
            $practiceUsers->reject(function ($user) {
                return $user->hasRole('administrator');
            })
                ->values();
        }

        $roles = Role::get()->keyBy('id');

        //Get the users that were as clinical emergency contacts from the locations page
        $existingUsers = $practiceUsers->map(function ($user) use (
            $primaryPractice,
            $roles
        ) {
            return $this->present($user, $primaryPractice, $roles);
        });

        return response()->json($existingUsers);
    }

    public function present(User $user, Practice $primaryPractice, Collection $roles)
    {
        $permissions = $user->practice($primaryPractice->id);
        $phone = $user->phoneNumbers->first();

        $roleId = $permissions->pivot->role_id
            ? $permissions->pivot->role_id
            : $user->roles->first()['id'];

        $forwardAlertsToContactUser = $user->forwardAlertsTo()
                ->having('name', '=', User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER)
                ->orHaving('name', '=', User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER)
                ->first()
            ?? null;

        $forwardCarePlanApprovalEmailsToContactUser = $user->forwardAlertsTo()
                ->having('name', '=', User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER)
                ->orHaving('name', '=', User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER)
                ->first()
            ?? null;

        return [
            'id'                                  => $user->id,
            'practice_id'                         => $primaryPractice->id,
            'email'                               => $user->email,
            'last_name'                           => $user->last_name,
            'first_name'                          => $user->first_name,
            'full_name'                           => $user->display_name,
            'phone_number'                        => $phone->number ?? '',
            'phone_extension'                     => $phone->extension ?? '',
            'phone_type'                          => array_search(
                $phone->type ?? '',
                PhoneNumber::getTypes()
            ) ?? '',
            'grantAdminRights'                    => $permissions->pivot->has_admin_rights ?? false,
            'sendBillingReports'                  => $permissions->pivot->send_billing_reports ?? false,
            'role_name'                           => $roles[$roleId]->name,
            'role_display_name'                   => $roles[$roleId]->display_name,
            'locations'                           => $user->locations->pluck('id'),
            'emr_direct_address'                  => $user->emr_direct_address,
            'forward_alerts_to'                   => [
                'who'     => $forwardAlertsToContactUser->pivot->name ?? 'billing_provider',
                'user_id' => $forwardAlertsToContactUser->id ?? null,
            ],
            'forward_careplan_approval_emails_to' => [
                'who'     => $forwardCarePlanApprovalEmailsToContactUser->pivot->name ?? 'billing_provider',
                'user_id' => $forwardCarePlanApprovalEmailsToContactUser->id ?? null,
            ],
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePracticeStaff $request, $primaryPracticeId, $userId)
    {
        $primaryPractice = Practice::find($primaryPracticeId);

        $formData = $request->input();

        $implementationLead = $primaryPractice->lead;

        $roles = Role::get()->keyBy('id');
        $userRole = $roles->keyBy('name')[$formData['role_name']];

        $user = User::updateOrCreate([
            'id' => $formData['id'],
        ], [
            'program_id'   => $primaryPractice->id,
            'email'        => $formData['email'],
            'first_name'   => $formData['first_name'],
            'last_name'    => $formData['last_name'],
            'display_name' => "{$formData['first_name']} {$formData['last_name']}",
            'user_status'  => 1,
        ]);

        $user->attachGlobalRole($userRole);

        if ($formData['emr_direct_address']) {
            $user->emr_direct_address = $formData['emr_direct_address'];
        }

        $grantAdminRights = false;
        if ($formData['grantAdminRights']) {
            $grantAdminRights = true;
        }

        $sendBillingReports = false;
        if ($formData['sendBillingReports']) {
            $sendBillingReports = true;
        }

        //Attach the locations
        $user->locations()->sync([]);
        $user->attachLocation($formData['locations']);

        $attachPractice = $user->attachPractice(
            $primaryPractice,
            $grantAdminRights,
            $sendBillingReports,
            $userRole->id
        );

        //attach phone
        $phone = $user->clearAllPhonesAndAddNewPrimary(
            $formData['phone_number'],
            $formData['phone_type'],
            true,
            $formData['phone_extension']
        );

        //clean up forwardAlertsTo before adding the new ones
        $user->forwardAlertsTo()->sync([]);

        if ($formData['forward_alerts_to']['who'] != 'billing_provider') {
            $user->forwardTo($formData['forward_alerts_to']['user_id'], $formData['forward_alerts_to']['who']);
        }

        if ($formData['role_name'] == 'provider') {
            $providerInfo = ProviderInfo::firstOrCreate([
                'user_id' => $user->id,
            ]);

            if ($formData['forward_careplan_approval_emails_to']['who'] != 'billing_provider') {
                $user->forwardTo(
                    $formData['forward_careplan_approval_emails_to']['user_id'],
                    $formData['forward_careplan_approval_emails_to']['who']
                );
            }
        }

//                $user->notify(new StaffInvite($implementationLead, $primaryPractice));

        return response()->json($this->present($user, $primaryPractice, $roles));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($practiceId, $userId)
    {
        $staff = User::find($userId);

        if ($staff) {
            $staff->delete();
        }

        return response()->json($staff);
    }
}
