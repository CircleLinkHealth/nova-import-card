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
use App\PhoneNumber;
use App\Practice;
use App\Role;
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
        $this->invites = $inviteRepository;
        $this->locations = $locationRepository;
        $this->practices = $practiceRepository;
        $this->users = $userRepository;
    }

    /**
     * Gets existing staff, and outputs them on window.cpm
     *
     * @param Practice $primaryPractice
     */
    public function getExistingStaff(Practice $primaryPractice)
    {
        //Get the users that were as clinical emergency contacts from the locations page
        $existingUsers = $primaryPractice->users->map(function ($user) {
            return [
                'id'                 => $user->id,
                'email'              => $user->email,
                'last_name'          => $user->last_name,
                'first_name'         => $user->first_name,
                'phone_number'       => $user->phoneNumbers->first()['number'] ?? '',
                'phone_type'         => array_search($user->phoneNumbers->first()['type'],
                        PhoneNumber::getTypes()) ?? '',
                'isComplete'         => false,
                'validated'          => false,
                'grandAdminRights'   => false,
                'sendBillingReports' => false,
                'errorCount'         => 0,
                'role_id'            => $user->roles->first()['id'] ?? 0,
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
        $roles = Role::whereIn('name', [
            'med_assistant',
            'office_admin',
            'practice-lead',
            'provider',
            'registered-nurse',
            'specialist',
        ])->get([
            'id',
            'display_name',
        ])
            ->sortBy('display_name');

        \JavaScript::put([
            'existingUsers' => $existingUsers,
            'locations'     => $locations,
            'locationIds'   => $locationIds,
            'phoneTypes'    => PhoneNumber::getTypes(),
            'roles'         => $roles->all(),
            //this will help us get role names on the views: rolesMap[id]
            'rolesMap'      => $roles->keyBy('id')->all(),
        ]);
    }

    /**
     * Gets existing locations, and outputs them on window.cpm
     *
     * @param Practice $primaryPractice
     */
    public function getExistingLocations(Practice $primaryPractice)
    {
        $existingLocations = $primaryPractice->locations->map(function ($loc) {

            $contactType = $loc->clinicalEmergencyContact->first()->pivot->name ?? null;
            $contactUser = $loc->clinicalEmergencyContact->first() ?? null;

            return [
                'id'               => $loc->id,
                'clinical_contact' => [
                    'email'     => $contactUser->email ?? null,
                    'firstName' => $contactUser->first_name ?? null,
                    'lastName'  => $contactUser->last_name ?? null,
                    'type'      => $contactType ?? 'billing_provider',
                ],
                'timezone'         => 'America/New_York',
                'ehr_password'     => $loc->ehr_password,
                'city'             => $loc->city,
                'address_line_1'   => $loc->address_line_1,
                'address_line_2'   => $loc->address_line_2,
                'ehr_login'        => $loc->ehr_login,
                'errorCount'       => 0,
                'isComplete'       => true,
                'name'             => $loc->name,
                'postal_code'      => $loc->postal_code,
                'state'            => $loc->state,
                'validated'        => true,
                'phone'            => $loc->phone,
            ];
        });


        \JavaScript::put([
            'existingLocations' => $existingLocations,
        ]);
    }

    public function postStoreLocations(
        Practice $primaryPractice,
        Request $request
    ) {
        foreach ($request->input('deleteTheseLocations') as $id) {
            $this->locations->delete($id);
        }

        $created = [];
        $i = 0;

        try {
            $sameEHRLogin = isset($request->input('locations')[0]['same_ehr_login']);
            $sameClinicalContact = isset($request->input('locations')[0]['same_clinical_contact']);

            foreach ($request->input('locations') as $index => $newLocation) {

                if (isset($newLocation['id'])) {
                    $location = $this->locations
                        ->skipPresenter()
                        ->update([
                            'practice_id'    => $primaryPractice->id,
                            'name'           => $newLocation['name'],
                            'phone'          => StringManipulation::formatPhoneNumber($newLocation['phone']),
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
                        ], $newLocation['id']);
                } else {
                    $location = $this->locations
                        ->skipPresenter()
                        ->create([
                            'practice_id'    => $primaryPractice->id,
                            'name'           => $newLocation['name'],
                            'phone'          => StringManipulation::formatPhoneNumber($newLocation['phone']),
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

                //If clinical contact is same for all, then get the data from the first location.
                if ($sameClinicalContact) {
                    $newLocation['clinical_contact']['type'] = $request->input('locations')[0]['clinical_contact']['type'];
                    $newLocation['clinical_contact']['email'] = $request->input('locations')[0]['clinical_contact']['email'];
                    $newLocation['clinical_contact']['firstName'] = $request->input('locations')[0]['clinical_contact']['firstName'];
                    $newLocation['clinical_contact']['lastName'] = $request->input('locations')[0]['clinical_contact']['lastName'];
                }

                if ($newLocation['clinical_contact']['type'] == CarePerson::BILLING_PROVIDER) {
                    //do nothing
                } else {
                    $user = $this->users->create([
                        'program_id' => $primaryPractice->id,
                        'email'      => $newLocation['clinical_contact']['email'],
                        'first_name' => $newLocation['clinical_contact']['firstName'],
                        'last_name'  => $newLocation['clinical_contact']['lastName'],
                        'password'   => 'password_not_set',
                    ]);

                    $user->attachPractice($primaryPractice);
                    $user->attachLocation($location);

                    $location->clinicalEmergencyContact()->attach($user->id, [
                        'name' => $newLocation['clinical_contact']['type'],
                    ]);
                }

                $i++;
            }
        } catch (ValidatorException $e) {
            $errors[] = [
                'index'    => $index,
                'messages' => $e->getMessageBag()->getMessages(),
                'input'    => $newLocation,
            ];
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

        $adminRole = Role::whereName('practice-lead')
            ->first();

        foreach ($request->input('deleteTheseUsers') as $id) {
            $this->users->delete($id);
        }

        $created = [];
        $i = 0;

        foreach ($request->input('users') as $index => $newUser) {
            //create the user
            try {
                if (isset($newUser['id'])) {
                    $user = $this->users
                        ->skipPresenter()
                        ->update([
                            'program_id'   => $primaryPractice->id,
                            'email'        => $newUser['email'],
                            'first_name'   => $newUser['first_name'],
                            'last_name'    => $newUser['last_name'],
                            'password'     => 'password_not_set',
                            'display_name' => "{$newUser['first_name']} {$newUser['last_name']}",
                        ], $newUser['id']);
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
                        ]);

                    $created[] = $i;
                }

                //Attach the role
                $user->roles()->attach($newUser['role_id']);

                if ($newUser['grandAdminRights']) {
                    $user->roles()->attach($adminRole);
                }

                //Attach the locations
                foreach ($newUser['locations'] as $locId) {
                    if (!$user->locations->contains($locId)) {
                        $user->locations()->attach($locId);
                    }
                }

                $attachPractice = $user->practices()->attach($primaryPractice->id);

                //attach phone
                $user->phoneNumbers()->create([
                    'number'     => StringManipulation::formatPhoneNumber($newUser['phone_number']),
                    'type'       => PhoneNumber::getTypes()[$newUser['phone_type']],
                    'is_primary' => true,
                ]);

//                $user->notify(new StaffInvite($implementationLead, $primaryPractice));
            } catch (\Exception $e) {
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