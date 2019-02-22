<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePracticeStaff;
use App\Permission;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\Role;
use App\User;
use Illuminate\Support\Collection;

class PracticeStaffController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     * @param int   $id
     * @param mixed $practiceId
     * @param mixed $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($practiceId, $userId)
    {
        $staff = User::find($userId);

        if ($staff) {
            $staff
                ->practices()
                ->detach($practiceId);

            if ($staff->program_id == $practiceId) {
                $staff->program_id = null;
                $staff->save();
            }
        }

        return response()->json($staff);
    }

    /**
     * Display a listing of the resource.
     *
     * @param mixed $primaryPracticeId
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
            'software-only',
            'care-center-external',
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

        if ( ! auth()->user()->isAdmin()) {
            $practiceUsers->reject(function ($user) {
                return $user->isAdmin();
            })
                ->values();
        }

        //Get the users that were as clinical emergency contacts from the locations page
        $existingUsers = $practiceUsers->map(function ($user) use (
            $primaryPractice
        ) {
            return $this->present($user, $primaryPractice);
        });

        return response()->json($existingUsers);
    }

    public function present(User $user, Practice $primaryPractice)
    {
        $permissions = $user->practice($primaryPractice->id);
        $phone       = $user->phoneNumbers->first();

        $roles = $user->rolesInPractice($primaryPractice->id);

        $forwardAlertsToContactUsers = $user->forwardAlertsTo()
            ->having('name', '=', User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER)
            ->orHaving('name', '=', User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER)
            ->get()
            ->mapToGroups(function ($user) {
                                                return [$user->pivot->name => $user->id];
                                            })
                                       ?? null;

        $forwardCarePlanApprovalEmailsToContactUsers = $user->forwardAlertsTo()
            ->having(
                                                                'name',
                                                                '=',
                                                                User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER
                                                            )
            ->orHaving(
                                                                'name',
                                                                '=',
                                                                User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER
                                                            )
            ->get()
            ->mapToGroups(function ($user) {
                                                                return [$user->pivot->name => $user->id];
                                                            })
                                                       ?? null;

        return [
            'id'              => $user->id,
            'practice_id'     => $primaryPractice->id,
            'email'           => $user->email,
            'last_name'       => $user->getLastName(),
            'first_name'      => $user->getFirstName(),
            'full_name'       => $user->display_name,
            'phone_number'    => $phone->number ?? '',
            'phone_extension' => $phone->extension ?? '',
            'phone_type'      => array_search(
                $phone->type ?? '',
                PhoneNumber::getTypes()
                                                     ) ?? '',
            'sendBillingReports'     => $permissions->pivot->send_billing_reports ?? false,
            'canApproveAllCareplans' => $user->canApproveCarePlans(),
            'role_names'             => $roles->map(function ($r) {
                return $r->name;
            }),
            'role_display_names' => $roles->implode('display_name', ', '),
            'locations'          => $user->locations->pluck('id'),
            'emr_direct_address' => $user->emr_direct_address,
            'forward_alerts_to'  => [
                'who'      => $forwardAlertsToContactUsers->keys()->first() ?? 'billing_provider',
                'user_ids' => $forwardAlertsToContactUsers->values()->first() ?? [],
            ],
            'forward_careplan_approval_emails_to' => [
                'who'      => $forwardCarePlanApprovalEmailsToContactUsers->keys()->first() ?? 'billing_provider',
                'user_ids' => $forwardCarePlanApprovalEmailsToContactUsers->values()->first() ?? [],
            ],
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     * @param mixed                    $primaryPracticeId
     * @param mixed                    $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePracticeStaff $request, $primaryPracticeId, $userId)
    {
        $primaryPractice = Practice::find($primaryPracticeId);

        $formData = $request->input();

        $implementationLead = $primaryPractice->lead;

        $roleNames = $formData['role_names'];
        $roles     = Role::whereIn('name', $roleNames)->get()->keyBy('id');

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

        if ($formData['emr_direct_address']) {
            $user->emr_direct_address = $formData['emr_direct_address'];
        }

        $careplanApprove = Permission::where('name', 'care-plan-approve')->first();
        if ($formData['canApproveAllCareplans']) {
            $user->attachPermission($careplanApprove->id);
        } elseif ($user->hasPermission('care-plan-approve')) {
            $user->detachPermission($careplanApprove->id);
        }

        //Attach the locations
        $user->locations()->sync([]);
        $user->attachLocation($formData['locations']);

        $sendBillingReports = false;
        if ($formData['sendBillingReports']) {
            $sendBillingReports = true;
        }

        $user->attachPractice($primaryPractice, $roles->keys(), $sendBillingReports);

        //attach phone
        $phone = $user->clearAllPhonesAndAddNewPrimary(
            $formData['phone_number'],
            $formData['phone_type'],
            true,
            $formData['phone_extension']
        );

        //clean up forwardAlertsTo before adding the new ones
        $user->forwardAlertsTo()->sync([]);

        if ('billing_provider' != $formData['forward_alerts_to']['who']) {
            foreach ($formData['forward_alerts_to']['user_ids'] as $user_id) {
                $user->forwardTo($user_id, $formData['forward_alerts_to']['who']);
            }
//            $user->forwardTo($formData['forward_alerts_to']['user_id'], $formData['forward_alerts_to']['who']);
        }

        if ('provider' == $formData['role_name']) {
            $providerInfo = ProviderInfo::firstOrCreate([
                'user_id' => $user->id,
            ]);

            if ('billing_provider' != $formData['forward_careplan_approval_emails_to']['who']) {
                foreach ($formData['forward_careplan_approval_emails_to']['user_ids'] as $user_id) {
                    $user->forwardTo($user_id, $formData['forward_careplan_approval_emails_to']['who']);
                }
//                $user->forwardTo(
//                    $formData['forward_careplan_approval_emails_to']['user_id'],
//                    $formData['forward_careplan_approval_emails_to']['who']
//                );
            }
        }

//                $user->notify(new StaffInvite($implementationLead, $primaryPractice));

        return response()->json($this->present($user, $primaryPractice, $roles));
    }
}
