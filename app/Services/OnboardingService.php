<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 13/02/2017
 * Time: 11:29 PM
 */

namespace App\Services;

use App\CarePerson;
use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Facades\StringManipulation;
use App\Location;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\Role;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

class OnboardingService
{
    /**
     * @var InviteRepository
     */
    protected $invites;

    /**
     * @var LocationRepository
     */
    protected $locations;

    /**
     * @var PracticeRepository
     */
    protected $practices;

    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * OnboardingController constructor.
     *
     * @param InviteRepository $inviteRepository
     * @param LocationRepository $locationRepository
     * @param PracticeRepository $practiceRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        InviteRepository $inviteRepository,
        LocationRepository $locationRepository,
        PracticeRepository $practiceRepository,
        UserRepository $userRepository
    ) {
        $this->invites   = $inviteRepository;
        $this->locations = $locationRepository;
        $this->practices = $practiceRepository;
        $this->users     = $userRepository;
    }

    /**
     * Gets existing staff, and outputs them on window.cpm
     *
     * @param Practice $primaryPractice
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

//        if (auth()->user()->hasRole('administrator')) {
//            $relevantRoles[] = 'administrator';
//        }

        $practiceUsers = User::ofType(array_merge($relevantRoles, ['practice-lead']))
                             ->whereHas('practices', function ($q) use (
                                 $primaryPractice
                             ) {
                                 $q->where('id', '=', $primaryPractice->id);
                             })
                             ->get()
                             ->sortBy('first_name')
                             ->values();

        if ( ! auth()->user()->hasRole('administrator')) {
            $practiceUsers->reject(function ($user) {
                return $user->hasRole('administrator');
            })
                          ->values();
        }

        //Get the users that were as clinical emergency contacts from the locations page
        $existingUsers = $practiceUsers->map(function ($user) use (
            $primaryPractice
        ) {
            $permissions = $user->practice($primaryPractice->id);
            $phone       = $user->phoneNumbers->first();

            $roleId = $permissions->pivot->role_id
                ? $permissions->pivot->role_id
                : $user->roles->first()['id'];

            $forwardAlertsToContactUser = $user->forwardAlertsTo()
                                               ->having('name', '=', User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER)
                                               ->orHaving('name', '=', User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER)
                                               ->first()
                                          ?? null;

            $forwardCarePlanApprovalEmailsToContactUser = $user->forwardAlertsTo()
                                                               ->having('name', '=',
                                                                   User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER)
                                                               ->orHaving('name', '=',
                                                                   User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER)
                                                               ->first()
                                                          ?? null;

            return [
                'id'                                  => $user->id,
                'email'                               => $user->email,
                'last_name'                           => $user->last_name,
                'first_name'                          => $user->first_name,
                'phone_number'                        => $phone->number ?? '',
                'phone_extension'                     => $phone->extension ?? '',
                'phone_type'                          => array_search(
                                                             $phone->type ?? '',
                                                             PhoneNumber::getTypes()
                                                         ) ?? '',
                'isComplete'                          => false,
                'validated'                           => false,
                'grantAdminRights'                    => $permissions->pivot->has_admin_rights ?? false,
                'sendBillingReports'                  => $permissions->pivot->send_billing_reports ?? false,
                'errorCount'                          => 0,
                'role_id'                             => $roleId,
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
            'rolesMap'      => $roles->keyBy('id')->all(),
        ];

        \JavaScript::put($result);

        return $result;
    }

    /**
     * Gets existing locations, and outputs them on window.cpm
     *
     * @param Practice $primaryPractice
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
                    'id'                        => $loc->id,
                    'clinical_contact'          => [
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
                    'phone'                     => StringManipulation::formatPhoneNumber($loc->phone),
                    'fax'                       => StringManipulation::formatPhoneNumber($loc->fax),
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

    public function postStoreLocations(
        Practice $primaryPractice,
        Request $request
    ) {
        foreach ($request->input('deleteTheseLocations') as $id) {
            $this->locations->delete($id);
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
                    $location = $this->locations
                        ->skipPresenter()
                        ->update([
                            'practice_id'    => $primaryPractice->id,
                            'name'           => $newLocation['name'],
                            'phone'          => StringManipulation::formatPhoneNumberE164($newLocation['phone']),
                            'fax'            => StringManipulation::formatPhoneNumberE164($newLocation['fax']),
                            'address_line_1' => $newLocation['address_line_1'],
                            'address_line_2' => $newLocation['address_line_2'] ?? null,
                            'city'           => $newLocation['city'],
                            'state'          => $newLocation['state'],
                            'timezone'       => $newLocation['timezone'],
                            'postal_code'    => $newLocation['postal_code'],
                            'ehr_login'      => $sameEHRLogin
                                ? $request->input('locations')[0]['ehr_login']
                                : $newLocation['ehr_login'] ?? null,
                            'ehr_password'   => $sameEHRLogin
                                ? $request->input('locations')[0]['ehr_password']
                                : $newLocation['ehr_password'] ?? null,
                        ], $newLocation['id']);
                } else {
                    $location = $this->locations
                        ->skipPresenter()
                        ->create([
                            'practice_id'    => $primaryPractice->id,
                            'name'           => $newLocation['name'],
                            'phone'          => StringManipulation::formatPhoneNumberE164($newLocation['phone']),
                            'fax'            => StringManipulation::formatPhoneNumberE164($newLocation['fax']),
                            'address_line_1' => $newLocation['address_line_1'],
                            'address_line_2' => $newLocation['address_line_2'],
                            'city'           => $newLocation['city'],
                            'state'          => $newLocation['state'],
                            'timezone'       => $newLocation['timezone'],
                            'postal_code'    => $newLocation['postal_code'],
                            'ehr_login'      => $sameEHRLogin
                                ? $request->input('locations')[0]['ehr_login']
                                : $newLocation['ehr_login'] ?? null,
                            'ehr_password'   => $sameEHRLogin
                                ? $request->input('locations')[0]['ehr_password']
                                : $newLocation['ehr_password'] ?? null,
                        ]);

                    $created[] = $i;
                }
            } catch (ValidatorException $e) {
                $errors[] = [
                    'index'    => $index,
                    'messages' => $e->getMessageBag()->getMessages(),
                    'input'    => $newLocation,
                ];
            }

            if (Location::where('practice_id', $primaryPractice->id)->count() == 1) {
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

            if ($newLocation['clinical_contact']['type'] == CarePerson::BILLING_PROVIDER) {
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
                        'input'    => $newLocation,
                    ];
                }

                if ( ! $clinicalContactUser) {
                    try {
                        $clinicalContactUser = $this->users->create([
                            'program_id' => $primaryPractice->id,
                            'email'      => $newLocation['clinical_contact']['email'],
                            'first_name' => $newLocation['clinical_contact']['first_name'],
                            'last_name'  => $newLocation['clinical_contact']['last_name'],
                            'password'   => 'password_not_set',
                        ]);

                        $clinicalContactUser->attachPractice($primaryPractice);
                        $clinicalContactUser->attachLocation($location);

                        //clean up other contacts before adding the new one
                        $location->clinicalEmergencyContact()->sync([]);

                        $location->clinicalEmergencyContact()->attach($clinicalContactUser->id, [
                            'name' => $newLocation['clinical_contact']['type'],
                        ]);
                    } catch (ValidatorException $e) {
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
            $i++;
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
                    $user = $this->users
                        ->skipPresenter()
                        ->update([
                            'program_id'   => $primaryPractice->id,
                            'email'        => $newUser['email'],
                            'first_name'   => $newUser['first_name'],
                            'last_name'    => $newUser['last_name'],
                            'display_name' => "{$newUser['first_name']} {$newUser['last_name']}",
                            'user_status'  => 1,
                        ], $newUser['id']);
                } elseif (User::whereEmail($newUser['email'])->first()) {
                    $user = User::whereEmail($newUser['email'])->first();
                } else {
                    $user = $this->users
                        ->skipPresenter()
                        ->create([
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

                $grantAdminRights = false;
                if ($newUser['grantAdminRights']) {
                    $grantAdminRights = true;
                }

                $sendBillingReports = false;
                if ($newUser['sendBillingReports']) {
                    $sendBillingReports = true;
                }

                //Attach the locations
                $user->attachLocation($newUser['locations']);

                $attachPractice = $user->attachPractice(
                    $primaryPractice,
                    $grantAdminRights,
                    $sendBillingReports,
                    $newUser['role_id']
                );

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

                if ($newUser['forward_alerts_to']['who'] != 'billing_provider') {
                    $user->forwardTo($newUser['forward_alerts_to']['user_id'], $newUser['forward_alerts_to']['who']);
                }

                if ($newUser['forward_careplan_approval_emails_to']['who'] != 'billing_provider') {
                    $user->forwardTo(
                        $newUser['forward_careplan_approval_emails_to']['user_id'],
                        $newUser['forward_careplan_approval_emails_to']['who']
                    );
                }

//                $user->notify(new StaffInvite($implementationLead, $primaryPractice));
            } catch (\Exception $e) {
                \Log::alert($e);
                if ($e instanceof QueryException) {
                    $errorCode = $e->errorInfo[1];
                    if ($errorCode == 1062) {
                        //do nothing
                        //we don't actually want to terminate the program if we detect duplicates
                        //we just don't wanna add the row again
                    }
                } elseif ($e instanceof ValidatorException) {
                    $errors[] = [
                        'index'    => $index,
                        'messages' => $e->getMessageBag()->getMessages(),
                        'input'    => $newUser,
                    ];
                }
            }

            $i++;
        }

        if (isset($errors)) {
            return response()->json([
                'errors'  => $errors,
                'created' => $created,
            ], 400);
        }
    }
}
