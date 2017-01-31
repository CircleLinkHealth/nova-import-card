<?php

namespace App\Http\Controllers;

use App\CarePerson;
use App\CLH\Facades\StringManipulation;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\Role;
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

    public function store(Request $request)
    {

        $input = $request->input();

        if ($request->ajax()) {

            $provider_user = new User();
            $provider_user->save();

            $provider = new ProviderInfo([
                'user_id' => $provider_user->id,
            ]);

            $role = Role::whereName('provider')->first();

            $provider_user->attachRole($role);

            $patient = User::find($input['patient_id']);

            //Care Team Add
            $care_team_member = new CarePerson([

                'user_id'        => $patient->id,
                'member_user_id' => $provider_user->id,
                'type'           => CarePerson::EXTERNAL,

            ]);

            $patient->careTeamMembers()->save($care_team_member);

            $provider_user->first_name = $input['first_name'];
            $provider_user->last_name = $input['last_name'];

            $provider_user->email = (isset($input['email']))
                ? $input['email']
                : '';

            if ($input['phone'] != '') {

                $phone = new PhoneNumber([

                    'user_id'    => $provider_user->id,
                    'type'       => 'work',
                    'number'     => $input['phone'],
                    'is_primary' => 1,

                ]);

                $phone->save();
                $provider_user->phoneNumbers()->save($phone);

            }

            $provider_user->address = (isset($input['address']))
                ? $input['address']
                : '';

            $provider->specialty = (isset($input['specialty']))
                ? $input['specialty']
                : '';
            $provider->qualification = (isset($input['type']))
                ? $input['type']
                : '';


            if ($input['practice'] != '') {

                $practice = Practice::where('display_name', '=', $input['practice'])->first();

                if (!$practice) {
                    $practice = new Practice();
                    $practice->display_name = $input['practice'];
                    $practice->name = str_slug($input['practice']);
                    $practice->save();
                }

                $provider_user->program_id = $practice->id;

            } else {

                $provider_user->program_id = null;

            }

            $provider->save();

            $provider_user->providerInfo()->save($provider);
            $provider_user->save();

            return json_encode([
                'message' => 'Created!',
                'name'    => $provider_user->fullName,
                'user_id' => $provider_user->id,
            ]);

        }

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


        if (str_contains($input['id'], 'new')) {
            $carePerson = CarePerson::create([
                'alert'          => $input['alert'],
                'type'           => snake_case($input['formatted_type']),
                'user_id'        => $patientId,
                'member_user_id' => $providerUser->id,
            ]);
        } else {
            $carePerson = CarePerson::where('id', '=', $input['id'])
                ->update([
                    'alert' => $input['alert'],
                    'type'  => snake_case($input['formatted_type']),
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

            $practice = Practice::updateOrCreate([
                'id' => $primaryPractice['id'],
            ], [
                'display_name' => $primaryPractice['display_name'],
            ]);
        }

        return response()->json(['carePerson' => $carePerson], 200);
    }

}
