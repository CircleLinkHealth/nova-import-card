<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\ProviderInfo;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CareTeamController extends Controller
{
    public function destroy(
        Request $request,
        $patientId,
        $memberId
    ) {
        if ( ! $request->ajax()) {
            return abort('403', 'Care Team Members cannot be deleted using this method');
        }

        $member = CarePerson::find($memberId);
        if ($member) {
            $member->delete();
        }

        return response()->json([], 200);
    }

    public function edit(Request $request, $patientId, $carePersonId)
    {
        $patient = User::find($patientId);

        $member = CarePerson::with([
            'user',
            'user.phoneNumbers',
            'user.providerInfo',
            'user.primaryPractice',
        ])
            ->whereId($carePersonId)
            ->first();

        $type = $member->type;

        if ($member->user->practice($patient->primaryPractice->id) && CarePerson::BILLING_PROVIDER != $member->type) {
            $type = $member->user->practiceOrGlobalRole()->display_name.' (Internal)';
        }

        $formattedType = snakeToSentenceCase($type);

        $phone = $member->user->phoneNumbers->where('is_primary', 1)->first();

        $memberUser = $member->user;

        $carePerson = [
            'id'                  => $member->id,
            'formatted_type'      => $formattedType,
            'alert'               => $member->alert,
            'is_billing_provider' => CarePerson::BILLING_PROVIDER == $type,
            'type'                => $type,
            'user'                => [
                'id'            => $memberUser->id,
                'email'         => $memberUser->email,
                'first_name'    => $memberUser->getFirstName(),
                'last_name'     => $memberUser->getLastname(),
                'suffix'        => $memberUser->getSuffix(),
                'address'       => $memberUser->address,
                'address2'      => $memberUser->address2,
                'city'          => $memberUser->city,
                'state'         => $memberUser->state,
                'zip'           => $memberUser->zip,
                'phone_numbers' => $phone
                    ? [
                        [
                            'id'     => $phone->id,
                            'number' => $phone->number,
                        ],
                    ]
                : [
                    [
                        'id'     => '',
                        'number' => '',
                    ],
                ],
                'primary_practice' => $memberUser->primaryPractice
                    ? [
                        'id'           => $memberUser->primaryPractice->id,
                        'display_name' => $memberUser->primaryPractice->display_name,
                    ]
                : [
                    'id'           => '',
                    'display_name' => '',
                ],
                'provider_info' => $memberUser->providerInfo
                    ? [
                        'id'          => $memberUser->providerInfo->id,
                        'is_clinical' => $memberUser->providerInfo->is_clinical,
                        'specialty'   => $memberUser->getSpecialty(),
                    ]
                : [
                    'id'          => '',
                    'is_clinical' => '',
                    'specialty'   => '',
                ],
            ],
        ];

        return response()->json($carePerson);
    }

    public function index(Request $request, $patientId)
    {
        $patient = User::find($patientId);

        $careTeam = CarePerson::whereHas('user', function ($q) {
            $q->with([
                'user',
                'user.phoneNumbers',
                'user.providerInfo',
                'user.primaryPractice',
            ]);
        })
            ->whereUserId($patient->id)
            ->orderBy('type')
            ->get()
            ->map(function ($member) use (
                                  $patient
                              ) {
                $type = $member->type;

                if ($member->user->practice($patient->primaryPractice->id) && ! in_array(
                    $member->type,
                    [CarePerson::BILLING_PROVIDER, CarePerson::REGULAR_DOCTOR]
                )) {
                    $formattedType = $member->user->practiceOrGlobalRole()->display_name.' (Internal)';
                }

                if ( ! isset($formattedType)) {
                    $formattedType = snakeToSentenceCase($type);
                }

                $phone = $member->user->phoneNumbers->where('is_primary', 1)->first();

                return [
                    'id'                  => $member->id,
                    'formatted_type'      => $formattedType,
                    'alert'               => $member->alert,
                    'is_billing_provider' => CarePerson::BILLING_PROVIDER == $type,
                    'type'                => $type,
                    'user_id'             => $member->user_id,
                    'user'                => [
                        'id'         => $member->user->id,
                        'email'      => $member->user->email,
                        'first_name' => $member->user->getFirstName(),
                        'last_name'  => $member->user->getLastName(),
                        'full_name'  => $member->user->getFullName(),
                        'suffix'     => optional($member->user->providerInfo)->is_clinical
                            ? $member->user->getSuffix()
                            : 'non-clinical',
                        'address'       => $member->user->address,
                        'address2'      => $member->user->address2,
                        'city'          => $member->user->city,
                        'state'         => $member->user->state,
                        'zip'           => $member->user->zip,
                        'phone_numbers' => $phone
                            ? [
                                [
                                    'id'     => $phone->id,
                                    'number' => $phone->number,
                                ],
                            ]
                        : [
                            [
                                'id'     => '',
                                'number' => '',
                            ],
                        ],
                        'primary_practice' => $member->user->primaryPractice
                            ? [
                                'id'           => $member->user->primaryPractice->id,
                                'display_name' => $member->user->primaryPractice->display_name,
                            ]
                        : [
                            'id'           => '',
                            'display_name' => '',
                        ],
                        'provider_info' => $member->user->providerInfo
                            ? [
                                'id'          => $member->user->providerInfo->id,
                                'is_clinical' => $member->user->providerInfo->is_clinical,
                                'specialty'   => $member->user->getSpecialty(),
                            ]
                        : [
                            'id'          => '',
                            'is_clinical' => '',
                            'specialty'   => '',
                        ],
                    ],
                ];
            });

        return response()->json($careTeam);
    }

    public function searchProviders(Request $request)
    {
        $firstNameTerm = $request->input('firstName');
        $lastNameTerm  = $request->input('lastName');

        $users = User::ofType([
            'med_assistant',
            'office_admin',
            'provider',
            'registered-nurse',
            'specialist',
        ])
            ->with('primaryPractice')
            ->with('providerInfo')
            ->with('phoneNumbers')
            ->where('first_name', 'like', "${firstNameTerm}%")
            ->where('last_name', 'like', "${lastNameTerm}%")
            ->get()
            ->map(function ($user) {
                //Add an empty phone number if there are none so that the front end doesn't break
                //v-model="newCarePerson.user.phone_numbers[0].number"
                if ($user->phoneNumbers->isEmpty()) {
                    $user->phoneNumbers->push(['id' => '', 'number' => '']);
                }

                return $user;
            });

        return response()->json(['results' => $users]);
    }

    public function update(
        Request $request,
        $id
    ) {
        if ( ! $request->ajax()) {
            return abort('403', 'Care Team Members cannot be deleted using this method');
        }

        $input     = $request->input();
        $patientId = $request->input('user_id');

        $patient = User::find($patientId);

        $userId   = $input['user']['id'];
        $userArgs = [
            'first_name' => $input['user']['first_name'],
            'last_name'  => $input['user']['last_name'],
            'suffix'     => $input['user']['suffix'] && 'non-clinical' == $input['user']['suffix']
                ? null
                : $input['user']['suffix'],
            'address'  => $input['user']['address'],
            'address2' => $input['user']['address2'],
            'city'     => $input['user']['city'],
            'state'    => $input['user']['state'],
            'zip'      => $input['user']['zip'],
            'email'    => $input['user']['email'],
        ];

        if (is_numeric($userId)) {
            $providerUser = User::updateOrCreate([
                'id' => $userId,
            ], $userArgs);
        } else {
            $providerUser = User::create($userArgs);
        }

        $type = Str::snake($input['formatted_type']);

        if (CarePerson::BILLING_PROVIDER == $type) {
            $existingCarePersonsOfSameType = $this->clearExistingCarePeopleWithSameType($type, $patient);

            $oldBillingProvider = $existingCarePersonsOfSameType->first();

            if ($oldBillingProvider) {
                //If the Billing Provider has changed, we want to reflect that change on the front end.
                if ($oldBillingProvider->id != $providerUser->id) {
                    $oldBillingProvider                 = $oldBillingProvider->fresh();
                    $oldBillingProvider->formatted_type = snakeToSentenceCase($oldBillingProvider->type);
                } else { //If it's the same, we'll return null
                    $oldBillingProvider = null;
                }
            }
        } elseif (CarePerson::REGULAR_DOCTOR == $type) {
            $existingCarePersonsOfSameType = $this->clearExistingCarePeopleWithSameType($type, $patient);

            $oldRegularDoctor = $existingCarePersonsOfSameType->first();

            if ($oldRegularDoctor) {
                //If the Regular Doctor has changed, we want to reflect that change on the front end.
                if ($oldRegularDoctor->id != $providerUser->id) {
                    $oldRegularDoctor                 = $oldRegularDoctor->fresh();
                    $oldRegularDoctor->formatted_type = snakeToSentenceCase($oldRegularDoctor->type);
                } else { //If it's the same, we'll return null
                    $oldRegularDoctor = null;
                }
            }
        }

        $alert = $input['alert'];

        if ( ! $providerUser->email) {
            $alert = false;
        }

        if ($providerUser->practice($patient->primaryPractice->id) && ! in_array(
            $type,
            [CarePerson::BILLING_PROVIDER, CarePerson::REGULAR_DOCTOR]
        )) {
            $type = $providerUser->practiceOrGlobalRole()->display_name.' (Internal)';
        }

        $carePerson = CarePerson::updateOrCreate([
            'user_id'        => $patientId,
            'member_user_id' => $providerUser->id,
        ], [
            'alert' => $alert,
            'type'  => $type,
        ]);

        if (isset($input['user']['phone_numbers'][0])) {
            $phone = $input['user']['phone_numbers'][0];

            if ( ! empty($phone['number'])) {
                if (isset($phone['id'])) {
                    $phone = PhoneNumber::where('id', '=', $phone['id'])
                        ->update([
                            'number' => (new StringManipulation())->formatPhoneNumber($phone['number']),
                        ]);
                } else {
                    $phone = PhoneNumber::updateOrCreate([
                        'user_id' => $providerUser->id,
                        'type'    => 'work',
                        'number'  => (new StringManipulation())->formatPhoneNumber($phone['number']),
                    ]);
                }
            }
        }

        if (isset($input['user']['provider_info'])) {
            $providerInfo = $input['user']['provider_info'];

            $newProviderInfoArgs            = [];
            $newProviderInfoArgs['user_id'] = $providerUser->id;
            if (array_key_exists('suffix', $input['user'])) {
                $newProviderInfoArgs['is_clinical'] = 'non-clinical' != $input['user']['suffix'];
            }
            if (array_key_exists('specialty', $providerInfo)) {
                $newProviderInfoArgs['specialty'] = $providerInfo['specialty'];
            }

            if (is_numeric($providerInfo['id'] ?? null)) {
                $provider = ProviderInfo::updateOrCreate([
                    'id'      => $providerInfo['id'],
                    'user_id' => $providerUser->id,
                ], $newProviderInfoArgs);
            } else {
                $provider = ProviderInfo::create($newProviderInfoArgs);
            }
        }

        if (isset($input['user']['primary_practice'])) {
            $primaryPractice = $input['user']['primary_practice'];

            if ($primaryPractice['display_name']) {
                if ( ! empty($primaryPractice['id'])) {
                    $practice = Practice::updateOrCreate([
                        'id' => $primaryPractice['id'],
                    ], [
                        'display_name' => $primaryPractice['display_name'],
                    ]);
                } else {
                    $practice = Practice::updateOrCreate([
                        'display_name' => $primaryPractice['display_name'],
                    ], [
                        'name' => Str::slug($primaryPractice['display_name']),
                    ]);
                }

                $providerUser->program_id = $practice->id;
                $providerUser->save();
            }
        }

        if (is_object($carePerson)) {
            $carePerson->load('user');
            $carePerson->formatted_type      = snakeToSentenceCase($carePerson->type);
            $carePerson->is_billing_provider = CarePerson::BILLING_PROVIDER == $carePerson->type;
        }

        return response()->json([
            'carePerson'         => $carePerson,
            'oldBillingProvider' => $oldBillingProvider ?? null,
            'oldRegularDoctor'   => $oldRegularDoctor ?? null,
        ], 200);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Support\Collection
     */
    private function clearExistingCarePeopleWithSameType(string $type, User $patient)
    {
        $acceptedTypes = [CarePerson::REGULAR_DOCTOR, CarePerson::BILLING_PROVIDER];

        if ( ! in_array($type, $acceptedTypes)) {
            throw new InvalidArgumentException("`${type}` is not an accepted type of CarePerson.");
        }

        //if other CarePersons with the same type exist, remove their type.
        $existingProvidersOfSameType = CarePerson::where('user_id', '=', $patient->id)
            ->where('type', '=', $type)
            ->get();

        foreach ($existingProvidersOfSameType as $existingProvider) {
            $existingProvider->type = 'external';

            if ($existingProvider->user && $existingProvider->user->practice($patient->primaryPractice->id)) {
                $existingProvider->type = 'internal';
            }

            $existingProvider->save();
        }

        return $existingProvidersOfSameType;
    }
}
