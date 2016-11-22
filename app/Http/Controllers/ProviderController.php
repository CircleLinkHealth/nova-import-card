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

            $user = new User();
            $user->save();
            
            $provider = new ProviderInfo([
                'user_id' => $user->id
            ]);

            $patient = User::find($input['patient_id'])->get();

            return $patient;

            //Care Team Add
            $care_team_member = new PatientCareTeamMember([

                'user_id' => $patient->id,
                'member_user_id' => $user->id,
                'type' => PatientCareTeamMember::EXTERNAL

            ]);

            $care_team_member->save();
            $user->patientCareTeamMembers()->save($care_team_member);

            $user->first_name = $input['first_name'];
            $user->last_name = $input['last_name'];

            $user->email = (isset($input['email'])) ? $input['email'] : '';

            if($input['phone'] != ''){

                $phone = new PhoneNumber([

                    'user_id' => $user->id,
                    'type' => 'work',
                    'is_primary' => 1

                ]);

                $phone->save();
                $user->phoneNumbers()->save($phone);

            }

            $user->address = (isset($input['address'])) ? $input['address'] : '';

            $provider->specialty = (isset($input['specialty'])) ? $input['specialty'] : '';
            $provider->qualification = (isset($input['type'])) ? $input['type'] : '';
            
            if($input['practice'] != ''){

                $practice = new Practice();
                $practice->display_name = $input['practice'];
                $practice->name = strtolower(str_replace(" ", "-", $input['practice']));
                $practice->save();

                $user->program_id = $practice->id;

            }

            $provider->save();

            $user->providerInfo()->save($provider);
            $user->save();

            return 'Created!';

        }

    }

}
