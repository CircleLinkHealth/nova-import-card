<?php

namespace App\Http\Controllers\API;

use App\CarePerson;
use App\CLH\Facades\StringManipulation;
use App\Http\Controllers\Controller;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\User;
use Illuminate\Http\Request;

class CareTeamController extends Controller
{
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

        if ($member->user->practice($patient->primaryPractice->id) && $member->type != CarePerson::BILLING_PROVIDER) {
            $type = $member->user->practiceOrGlobalRole()->display_name . " (Internal)";
        }

        $formattedType = snakeToSentenceCase($type);

        $phone = $member->user->phoneNumbers->where('is_primary', 1)->first();

        $carePerson = [
            'id'                  => $member->id,
            'formatted_type'      => $formattedType,
            'alert'               => $member->alert,
            'is_billing_provider' => $type == CarePerson::BILLING_PROVIDER,
            'user'                => [
                'id'               => $member->user->id,
                'email'            => $member->user->email,
                'first_name'       => $member->user->first_name,
                'last_name'        => $member->user->last_name,
                'address'          => $member->user->address,
                'address2'         => $member->user->address2,
                'city'             => $member->user->city,
                'state'            => $member->user->state,
                'zip'              => $member->user->zip,
                'phone_numbers'    => $phone
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
                'provider_info'    => $member->user->providerInfo
                    ? [
                        'id'            => $member->user->providerInfo->id,
                        'is_clinical' => $member->user->providerInfo->is_clinical,
                        'specialty'     => $member->user->providerInfo->specialty,
                    ]
                    : [
                        'id'            => '',
                        'is_clinical' => '',
                        'specialty'     => '',
                    ]
                ,
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

                if ($member->user->practice($patient->primaryPractice->id) && $member->type != CarePerson::BILLING_PROVIDER) {
                    $type = $member->user->practiceOrGlobalRole()->display_name . " (Internal)";
                }

                $formattedType = snakeToSentenceCase($type);

                $phone = $member->user->phoneNumbers->where('is_primary', 1)->first();

                return [
                    'id'                  => $member->id,
                    'formatted_type'      => $formattedType,
                    'alert'               => $member->alert,
                    'is_billing_provider' => $type == CarePerson::BILLING_PROVIDER,
                    'user_id'             => $member->user_id,
                    'user'                => [
                        'id'               => $member->user->id,
                        'email'            => $member->user->email,
                        'first_name'       => $member->user->first_name,
                        'last_name'        => $member->user->last_name,
                        'address'          => $member->user->address,
                        'address2'         => $member->user->address2,
                        'city'             => $member->user->city,
                        'state'            => $member->user->state,
                        'zip'              => $member->user->zip,
                        'phone_numbers'    => $phone
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
                        'provider_info'    => $member->user->providerInfo
                            ? [
                                'id'            => $member->user->providerInfo->id,
                                'is_clinical' => $member->user->providerInfo->is_clinical,
                                'specialty'     => $member->user->providerInfo->specialty,
                            ]
                            : [
                                'id'            => '',
                                'is_clinical' => '',
                                'specialty'     => '',
                            ]
                        ,
                    ],
                ];
            });

        return response()->json($careTeam);
    }

    public function destroy(
        Request $request,
        $patientId,
        $memberId
    ) {

        if (!$request->ajax()) {
            return abort('403', 'Care Team Members cannot be deleted using this method');
        }

        $member = CarePerson::find($memberId);
        if ($member) {
            $member->delete();
        }

        return response()->json([], 200);
    }

    public function searchProviders(Request $request)
    {
        $firstNameTerm = $request->input('firstName');
        $lastNameTerm = $request->input('lastName');

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
            ->where('first_name', 'like', "$firstNameTerm%")
            ->where('last_name', 'like', "$lastNameTerm%")
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

        if (!$request->ajax()) {
            return abort('403', 'Care Team Members cannot be deleted using this method');
        }

        $input = $request->input();
        $patientId = $request->input('user_id');

        $patient = User::find($patientId);

        $providerUser = User::updateOrCreate([
            'id' => $input['user']['id'],
        ], [
            'first_name' => $input['user']['first_name'],
            'last_name'  => $input['user']['last_name'],
            'address'    => $input['user']['address'],
            'address2'   => $input['user']['address2'],
            'city'       => $input['user']['city'],
            'state'      => $input['user']['state'],
            'zip'        => $input['user']['zip'],
            'email'      => $input['user']['email'],
        ]);

        $type = $input['is_billing_provider']
            ? CarePerson::BILLING_PROVIDER
            : snake_case($input['formatted_type']);

        if ($type == CarePerson::BILLING_PROVIDER) {
            $billingProvider = CarePerson::where('user_id', '=', $patientId)
                ->where('type', '=', CarePerson::BILLING_PROVIDER)
                ->first();

            //then get rid of other billing providers
            $oldBillingProviders = CarePerson::where('user_id', '=', $patientId)
                ->where('type', '=', CarePerson::BILLING_PROVIDER)
                ->get();

            foreach ($oldBillingProviders as $oldBillingProvider) {
                $oldBillingProvider->type = 'external';

                if ($oldBillingProvider->user && $oldBillingProvider->user->practice($patient->primaryPractice->id)) {
                    $oldBillingProvider->type = $oldBillingProvider->user->practiceOrGlobalRole();
                }

                $oldBillingProvider->save();
            }

            //If the Billing Provider has changed, we want to reflect that change on the front end.
            //If it's the same, we'll return null
            if ($billingProvider) {
                $billingProvider = $billingProvider->id == $providerUser->id
                    ? null
                    : $billingProvider;
            }
        }

        $alert = $input['alert'];

        if (!$providerUser->email) {
            $alert = false;
        }

        if ($providerUser->practice($patient->primaryPractice->id) && $type != CarePerson::BILLING_PROVIDER) {
            $type = $providerUser->practiceOrGlobalRole()->display_name . " (Internal)";
        }

        if (str_contains($input['id'], 'new')) {
            $carePerson = CarePerson::create([
                'alert'          => $alert,
                'type'           => $type,
                'user_id'        => $patientId,
                'member_user_id' => $providerUser->id,
            ]);
        } else {
            $carePerson = CarePerson::where('id', '=', $input['id'])
                ->with('user')
                ->first();

            $carePerson->alert = $alert;
            $carePerson->type = $type;
            $carePerson->save();
        }

        if (isset($input['user']['phone_numbers'][0])) {
            $phone = $input['user']['phone_numbers'][0];

            if (isset($phone['id'])) {
                $phone = PhoneNumber::where('id', '=', $phone['id'])
                    ->update([
                        'number' => StringManipulation::formatPhoneNumber($phone['number']),
                    ]);
            } else {
                $phone = PhoneNumber::updateOrCreate([
                    'user_id' => $providerUser->id,
                    'type'    => 'work',
                    'number'  => StringManipulation::formatPhoneNumber($phone['number']),
                ]);
            }
        }

        if (isset($input['user']['provider_info'])) {
            $providerInfo = $input['user']['provider_info'];

            $args = [];
            if (array_key_exists('is_clinical', $providerInfo)) {
                $args['is_clinical'] = $providerInfo['is_clinical'];
            }
            if (array_key_exists('specialty', $providerInfo)) {
                $args['specialty'] = $providerInfo['specialty'];
            }

            $provider = ProviderInfo::updateOrCreate([
                'id'      => $providerInfo['id'] ?? null,
                'user_id' => $providerUser->id,
            ], $args);
        }

        if (isset($input['user']['primary_practice'])) {
            $primaryPractice = $input['user']['primary_practice'];

            if ($primaryPractice['display_name']) {
                if (!empty($primaryPractice['id'])) {
                    $practice = Practice::updateOrCreate([
                        'id' => $primaryPractice['id'],
                    ], [
                        'display_name' => $primaryPractice['display_name'],
                    ]);
                } else {
                    $practice = Practice::updateOrCreate([
                        'display_name' => $primaryPractice['display_name'],
                    ], [
                        'name' => str_slug($primaryPractice['display_name']),
                    ]);
                }

                $providerUser->program_id = $practice->id;
                $providerUser->save();
            }
        }

        if (is_object($carePerson)) {
            $carePerson->load('user');
            $carePerson->formatted_type = snakeToSentenceCase($carePerson->type);
        }

        return response()->json([
            'carePerson'         => $carePerson,
            'oldBillingProvider' => $billingProvider ?? null,
        ], 200);
    }

}
