<?php

namespace App\Http\Controllers;

use App\PatientCareTeamMember;
use App\PatientInfo;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\User;
use Illuminate\Http\Request;

class ProviderController extends Controller
{

    public function store(Request $request){

        $input = $request->input();

        if($request->ajax()){

            $provider_user = new User();
            $provider_user->save();
            
            $provider = new ProviderInfo([
                'user_id' => $provider_user->id
            ]);

            $patient = User::find($input['patient_id']);

            //Care Team Add
            $care_team_member = new PatientCareTeamMember([

                'user_id' => $patient->id,
                'member_user_id' => $provider_user->id,
                'type' => PatientCareTeamMember::EXTERNAL

            ]);

            $provider_user->patientCareTeamMembers()->save($care_team_member);

            $provider_user->first_name = $input['first_name'];
            $provider_user->last_name = $input['last_name'];

            $provider_user->email = (isset($input['email'])) ? $input['email'] : '';

            if($input['phone'] != ''){

                $phone = new PhoneNumber([

                    'user_id' => $provider_user->id,
                    'type' => 'work',
                    'number' => $input['phone'],
                    'is_primary' => 1

                ]);

                $phone->save();
                $provider_user->phoneNumbers()->save($phone);

            }

            $provider_user->address = (isset($input['address'])) ? $input['address'] : '';

            $provider->specialty = (isset($input['specialty'])) ? $input['specialty'] : '';
            $provider->qualification = (isset($input['type'])) ? $input['type'] : '';
            
            if($input['practice'] != ''){

                $practice = new Practice();
                $practice->display_name = $input['practice'];
                $practice->name = strtolower(str_replace(" ", "-", $input['practice']));
                $practice->save();

                $provider_user->program_id = $practice->id;

            }

            $provider->save();

            $provider_user->providerInfo()->save($provider);
            $provider_user->save();

            return json_encode([
                'message' => 'Created!',
                'name' => $provider_user->fullName,
                'user_id' => $provider_user->id
            ]);

        }

    }

}
