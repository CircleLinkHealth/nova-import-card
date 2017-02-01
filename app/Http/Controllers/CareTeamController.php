<?php

namespace App\Http\Controllers;

use App\CarePerson;
use App\CLH\Facades\StringManipulation;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\User;
use Illuminate\Http\Request;

class CareTeamController extends Controller
{
    public function destroy(
        Request $request,
        $id
    ) {

        if (!$request->ajax()) {
            return abort('403', 'Care Team Members cannot be deleted using this method');
        }

        $member = CarePerson::find($id);
        if ($member) {
            $member->delete();
        }

        return response()->json([], 200);
    }

    public function searchProviders(Request $request)
    {
        $firstNameTerm = $request->input('firstName');
        $lastNameTerm = $request->input('lastName');

        $users = User::ofType(['provider'])
            ->with('primaryPractice')
            ->with('providerInfo')
            ->with('phoneNumbers')
            ->where('first_name', 'like', "$firstNameTerm%")
            ->where('last_name', 'like', "$lastNameTerm%")
            ->get();

        return response()->json(['results' => $users]);
    }

    public function update(
        Request $request,
        $id
    ) {

        if (!$request->ajax()) {
            return abort('403', 'Care Team Members cannot be deleted using this method');
        }

        $input = $request->input('careTeamMember');
        $patientId = $request->input('patientId');

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

        if (str_contains($input['id'], 'new')) {
            $carePerson = CarePerson::create([
                'alert'          => $input['alert'],
                'type'           => $type,
                'user_id'        => $patientId,
                'member_user_id' => $providerUser->id,
            ]);
        } else {
            $carePerson = CarePerson::where('id', '=', $input['id'])
                ->update([
                    'alert' => $input['alert'],
                    'type'  => $type,
                ]);
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

            $provider = ProviderInfo::updateOrCreate([
                'id'      => $providerInfo['id'],
                'user_id' => $providerUser->id,
            ], [
                'qualification' => $providerInfo['qualification'],
                'specialty'     => $providerInfo['specialty'],
            ]);
        }

        if (isset($input['user']['primary_practice'])) {
            $primaryPractice = $input['user']['primary_practice'];

            if ($primaryPractice['display_name']) {
                $practice = Practice::updateOrCreate([
                    'id' => $primaryPractice['id'],
                ], [
                    'display_name' => $primaryPractice['display_name'],
                ]);

                $providerUser->program_id = $practice->id;
                $providerUser->save();
            }
        }

        if (is_object($carePerson)) {
            $carePerson->load('user');
        }

        return response()->json(['carePerson' => $carePerson], 200);
    }

}
