<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\PracticeSettings\Services;

use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\ProviderInfo;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

class OnboardingService
{
    /**
     * @var \CircleLinkHealth\Core\StringManipulation
     */
    protected $stringManipulation;

    /**
     * OnboardingService constructor.
     */
    public function __construct(StringManipulation $stringManipulation)
    {
        $this->stringManipulation = $stringManipulation;
    }

    /**
     * Gets existing locations, and outputs them on window.cpm.
     */
    public function getExistingLocations(Practice $primaryPractice)
    {
        $existingLocations = $primaryPractice->locations
            ->sortBy('name')
            ->map(function ($loc) use (
                $primaryPractice
            ) {
                $contactType = $loc->clinicalEmergencyContact->first()->pivot->name ?? null;
                $contactUser = $loc->clinicalEmergencyContact->first() ?? null;

                return [
                    'id'               => $loc->id,
                    'clinical_contact' => [
                        'email'      => $contactUser->email ?? null,
                        'first_name' => $contactUser->first_name ?? null,
                        'last_name'  => $contactUser->last_name ?? null,
                        'type'       => $contactType ?? 'billing_provider',
                    ],
                    'is_primary'                => $loc->is_primary,
                    'timezone'                  => $loc->timezone ?? 'America/New_York',
                    'ehr_password'              => $loc->ehr_password,
                    'city'                      => $loc->city,
                    'address_line_1'            => $loc->address_line_1,
                    'address_line_2'            => $loc->address_line_2,
                    'ehr_login'                 => $loc->ehr_login,
                    'errorCount'                => 0,
                    'isComplete'                => true,
                    'name'                      => $loc->name,
                    'postal_code'               => $loc->postal_code,
                    'state'                     => $loc->state,
                    'validated'                 => true,
                    'phone'                     => $this->stringManipulation->formatPhoneNumber($loc->phone),
                    'fax'                       => $this->stringManipulation->formatPhoneNumber($loc->fax),
                    'emr_direct_address'        => $loc->emr_direct_address,
                    'sameClinicalIssuesContact' => $primaryPractice->same_clinical_contact,
                    'sameEHRLogin'              => $primaryPractice->same_ehr_login,
                ];
            });

        \JavaScript::put([
            'existingLocations' => $existingLocations,
        ]);

        return $existingLocations;
    }

    /**
     * Gets existing staff, and outputs them on window.cpm.
     */
    public function getExistingStaff(Practice $primaryPractice)
    {
        $relevantRoles = [
            'med_assistant',
            'office_admin',
            'provider',
            'registered-nurse',
            'specialist',
        ];

//        if (auth()->user()->isAdmin()) {
//            $relevantRoles[] = 'administrator';
//        }

        $practiceUsers = User::ofType(array_merge($relevantRoles, ['practice-lead']))
            ->whereHas('practices', function ($q) use (
                                 $primaryPractice
                             ) {
                $q->where('practices.id', '=', $primaryPractice->id);
            })
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
                ->first()
                                                          ?? null;

            return [
                'id'              => $user->id,
                'email'           => $user->email,
                'last_name'       => $user->last_name,
                'first_name'      => $user->first_name,
                'phone_number'    => $phone->number ?? '',
                'phone_extension' => $phone->extension ?? '',
                'phone_type'      => array_search(
                    $phone->type ?? '',
                    PhoneNumber::getTypes()
                ) ?? '',
                'isComplete'         => false,
                'validated'          => false,
                'sendBillingReports' => $permissions->pivot->send_billing_reports ?? false,
                'errorCount'         => 0,
                'role_id'            => $roleId,
                'locations'          => $user->locations->pluck('id'),
                'emr_direct_address' => $user->emr_direct_address,
                'forward_alerts_to'  => [
                    'who'     => $forwardAlertsToContactUser->pivot->name ?? 'billing_provider',
                    'user_id' => $forwardAlertsToContactUser->id ?? null,
                ],
                'forward_careplan_approval_emails_to' => [
                    'who'     => $forwardCarePlanApprovalEmailsToContactUser->pivot->name ?? 'billing_provider',
                    'user_id' => $forwardCarePlanApprovalEmailsToContactUser->id ?? null,
                ],
            ];
        });

        $locations = $primaryPractice->locations->map(function ($loc) {
            return [
                'id'   => $loc->id,
                'name' => $loc->name,
            ];
        });

        $locationIds = $primaryPractice->locations->map(function ($loc) {
            return $loc->id;
        });

        //get the relevant roles
        $roles = Role::whereIn('name', $relevantRoles)
            ->get([
                'id',
                'display_name',
            ])
            ->sortBy('display_name');

        $result = [
            'existingUsers' => $existingUsers,
            'locations'     => $locations,
            'locationIds'   => $locationIds,
            'phoneTypes'    => PhoneNumber::getTypes(),
            'roles'         => $roles->all(),
            //this will help us get role names on the views: rolesMap[id]
            'rolesMap' => $roles->keyBy('id')->all(),
        ];

        \JavaScript::put($result);

        return $result;
    }

    public function postStoreLocations(
        Practice $primaryPractice,
        Request $request
    ) {
        foreach ($request->input('deleteTheseLocations') as $id) {
            Location::delete($id);
        }

        $created = [];
        $i       = 0;

        $sameClinicalContact = $request->input('sameClinicalIssuesContact');
        $sameEHRLogin        = $request->input('sameEHRLogin');

        foreach ($request->input('locations') as $index => $newLocation) {
            if ( ! $newLocation['name']) {
                continue;
            }

            try {
                if (isset($newLocation['id'])) {
                    $location = Location::update([
                        'practice_id'    => $primaryPractice->id,
                        'name'           => $newLocation['name'],
                        'phone'          => $this->stringManipulation->formatPhoneNumberE164($newLocation['phone']),
                        'fax'            => $this->stringManipulation->formatPhoneNumberE164($newLocation['fax']),
                        'address_line_1' => $newLocation['address_line_1'],
                        'address_line_2' => $newLocation['address_line_2'] ?? null,
                        'city'           => $newLocation['city'],
                        'state'          => $newLocation['state'],
                        'timezone'       => $newLocation['timezone'],
                        'postal_code'    => $newLocation['postal_code'],
                        'ehr_login'      => $sameEHRLogin
                            ? $request->input('locations')[0]['ehr_login']
                            : $newLocation['ehr_login'] ?? null,
                        'ehr_password' => $sameEHRLogin
                            ? $request->input('locations')[0]['ehr_password']
                            : $newLocation['ehr_password'] ?? null,
                    ], $newLocation['id']);
                } else {
                    $args = [
                        'practice_id'    => $primaryPractice->id,
                        'name'           => $newLocation['name'],
                        'phone'          => $this->stringManipulation->formatPhoneNumberE164($newLocation['phone']),
                        'fax'            => $this->stringManipulation->formatPhoneNumberE164($newLocation['fax']),
                        'address_line_1' => $newLocation['address_line_1'],
                        'address_line_2' => $newLocation['address_line_2'],
                        'city'           => $newLocation['city'],
                        'state'          => $newLocation['state'],
                        'timezone'       => $newLocation['timezone'],
                        'postal_code'    => $newLocation['postal_code'],
                        'ehr_login'      => $sameEHRLogin
                            ? $request->input('locations')[0]['ehr_login']
                            : $newLocation['ehr_login'] ?? null,
                        'ehr_password' => $sameEHRLogin
                            ? $request->input('locations')[0]['ehr_password']
                            : $newLocation['ehr_password'] ?? null,
                    ];

                    Validator::validate($args, [
                        'practice_id'    => 'required|exists:practices,id',
                        'name'           => 'required',
                        'phone'          => 'required',
                        'address_line_1' => 'required',
                        'address_line_2' => '',
                        'city'           => 'required',
                        'state'          => 'required',
                        'timezone'       => 'required',
                        'postal_code'    => 'required',
                        'billing_code'   => 'required',
                    ]);

                    $location = Location::create($args);

                    $created[] = $i;
                }
            } catch (ValidationException $e) {
                $errors[] = [
                    'index'    => $index,
                    'messages' => $e->errors(),
                    'input'    => $newLocation,
                ];
            }

            if (1 == Location::where('practice_id', $primaryPractice->id)->count()) {
                $location->is_primary = 1;
                $location->save();
            }

            $location->emr_direct_address           = $newLocation['emr_direct_address'];
            $primaryPractice->same_clinical_contact = false;

            //If clinical contact is same for all, then get the data from the first location.
            if ($sameClinicalContact) {
                $newLocation['clinical_contact']['type']      = $request->input('locations')[0]['clinical_contact']['type'];
                $newLocation['clinical_contact']['email']     = $request->input('locations')[0]['clinical_contact']['email'];
                $newLocation['clinical_contact']['firstName'] = $request->input('locations')[0]['clinical_contact']['firstName'];
                $newLocation['clinical_contact']['lastName']  = $request->input('locations')[0]['clinical_contact']['lastName'];

                $primaryPractice->same_clinical_contact = true;
            }

            $primaryPractice->same_ehr_login = false;

            if ($sameEHRLogin) {
                $primaryPractice->same_ehr_login = true;
            }

            $primaryPractice->save();

            if (CarePerson::BILLING_PROVIDER == $newLocation['clinical_contact']['type']) {
                //clean up other contacts, just in case this was just set as the billing provider
                $location->clinicalEmergencyContact()->sync([]);
            } else {
                $clinicalContactUser = User::whereEmail($newLocation['clinical_contact']['email'])
                    ->first();

                if ( ! $newLocation['clinical_contact']['email']) {
                    $clinicalContactUser = null;

                    $errors[] = [
                        'index'    => $index,
                        'messages' => [
                            'email' => ['Clinical Contact email is a required field.'],
                        ],
                        'input' => $newLocation,
                    ];
                }

                if ( ! $clinicalContactUser) {
                    try {
                        $args = [
                            'program_id' => $primaryPractice->id,
                            'email'      => $newLocation['clinical_contact']['email'],
                            'first_name' => $newLocation['clinical_contact']['first_name'],
                            'last_name'  => $newLocation['clinical_contact']['last_name'],
                            'password'   => 'password_not_set',
                        ];

                        Validator::validate($args, [
                            'email'      => 'required|email|unique:users,email',
                            'first_name' => 'required',
                            'last_name'  => 'required',
                            'password'   => 'required|min:8',
                        ]);

                        $clinicalContactUser = User::create($args);

                        $clinicalContactUser->attachPractice($primaryPractice, []);
                        $clinicalContactUser->attachLocation($location);

                        //clean up other contacts before adding the new one
                        $location->clinicalEmergencyContact()->sync([]);

                        $location->clinicalEmergencyContact()->attach($clinicalContactUser->id, [
                            'name' => $newLocation['clinical_contact']['type'],
                        ]);
                    } catch (ValidationException $e) {
                        $errors[] = [
                            'index'    => $index,
                            'messages' => $e->getMessageBag()->getMessages(),
                            'input'    => $newLocation,
                        ];
                    }
                }
            }

            if ($primaryPractice->lead) {
                $primaryPractice->lead->attachLocation($location);
            }
            ++$i;
        }

        if (isset($errors)) {
            return response()->json([
                'errors'  => $errors,
                'created' => $created,
            ], 400);
        }
    }

    public function postStoreStaff(
        Practice $primaryPractice,
        Request $request
    ) {
        $implementationLead = $primaryPractice->lead;

        foreach ($request->input('deleteTheseUsers') as $id) {
            $detachUser = User::find($id);
            $detachUser->practices()->detach($primaryPractice->id);
        }

        $created = [];
        $i       = 0;

        foreach ($request->input('users') as $index => $newUser) {
            //create the user
            try {
                if ( ! $newUser['first_name'] && ! $newUser['last_name']) {
                    continue;
                }

                if (isset($newUser['id'])) {
                    $user = User::update([
                        'program_id'   => $primaryPractice->id,
                        'email'        => $newUser['email'],
                        'first_name'   => $newUser['first_name'],
                        'last_name'    => $newUser['last_name'],
                        'display_name' => "{$newUser['first_name']} {$newUser['last_name']}",
                        'user_status'  => 1,
                    ], $newUser['id']);
                } elseif ($user = User::whereEmail($newUser['email'])->first() && ! empty($user)) {
                    //assignment happened in else if clause
                } else {
                    $user = User::create([
                        'program_id'   => $primaryPractice->id,
                        'email'        => $newUser['email'],
                        'first_name'   => $newUser['first_name'],
                        'last_name'    => $newUser['last_name'],
                        'password'     => 'password_not_set',
                        'display_name' => "{$newUser['first_name']} {$newUser['last_name']}",
                        'user_status'  => 1,
                    ]);

                    $user->attachGlobalRole($newUser['role_id']);

                    $created[] = $i;
                }

                $user->emr_direct_address = $newUser['emr_direct_address'];

                //Attach the locations
                $user->attachLocation($newUser['locations']);

                $sendBillingReports = false;
                if ($newUser['sendBillingReports']) {
                    $sendBillingReports = true;
                }

                $user->attachPractice($primaryPractice, [$newUser['role_id']], $sendBillingReports);

                //attach phone
                $phone = $user->clearAllPhonesAndAddNewPrimary(
                    $newUser['phone_number'],
                    $newUser['phone_type'],
                    true,
                    $newUser['phone_extension']
                );

                $providerRole = Role::whereName('provider')->first();

                if ($newUser['role_id'] == $providerRole->id) {
                    $providerInfoCreated = ProviderInfo::firstOrCreate([
                        'user_id' => $user->id,
                    ]);
                }

                //clean up other contacts before adding the new one
                $user->forwardAlertsTo()->sync([]);

                if ('billing_provider' != $newUser['forward_alerts_to']['who']) {
                    $user->forwardTo($newUser['forward_alerts_to']['user_id'], $newUser['forward_alerts_to']['who']);
                }

                if ('billing_provider' != $newUser['forward_careplan_approval_emails_to']['who']) {
                    $user->forwardTo(
                        $newUser['forward_careplan_approval_emails_to']['user_id'],
                        $newUser['forward_careplan_approval_emails_to']['who']
                    );
                }
//                $user->notify(new StaffInvite($implementationLead, $primaryPractice));
            } catch (\Exception $e) {
                \Log::alert($e);
                if ($e instanceof QueryException) {
                    //                    @todo:heroku query to see if it exists, then attach

                    $errorCode = $e->errorInfo[1];
                    if (1062 == $errorCode) {
                        //do nothing
                        //we don't actually want to terminate the program if we detect duplicates
                        //we just don't wanna add the row again
                    }
                } elseif ($e instanceof ValidationException) {
                    $errors[] = [
                        'index'    => $index,
                        'messages' => $e->getMessageBag()->getMessages(),
                        'input'    => $newUser,
                    ];
                }
            }

            ++$i;
        }

        if (isset($errors)) {
            return response()->json([
                'errors'  => $errors,
                'created' => $created,
            ], 400);
        }
    }
}
