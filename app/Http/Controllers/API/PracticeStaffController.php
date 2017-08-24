<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\PhoneNumber;
use App\Practice;
use App\Role;
use App\User;
use Illuminate\Http\Request;
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
            'phone_type'                          => array_search($phone->type ?? '',
                    PhoneNumber::getTypes()) ?? '',
            'grandAdminRights'                    => $permissions->pivot->has_admin_rights ?? false,
            'sendBillingReports'                  => $permissions->pivot->send_billing_reports ?? false,
            'role_id'                             => $roleId,
            'role_name'                           => $roles[$roleId]->display_name,
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
    public function destroy($practiceId, $userId)
    {
        $staff = User::find($userId);

        if ($staff) {
            $staff->delete();
        }

        return response()->json($staff);
    }
}
