<?php

namespace App\Http\Controllers;

use App\Family;
use App\PatientInfo;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class FamilyController extends Controller
{

    public function create(){

        $wpUsers = PatientInfo::enrolled()->pluck('user_id');

        $users = User::whereIn( 'ID', Auth::user()->viewableUserIds() )->OrderBy( 'id', 'desc' )->get()->lists( 'fullNameWithId', 'ID' )->all();

        $filterUser = 'all';
        if ( !empty($params[ 'filterUser' ]) ) {
            $filterUser = $params[ 'filterUser' ];
            if ( $params[ 'filterUser' ] != 'all' ) {
                $wpUsers->where( 'ID', '=', $filterUser );
            }
        }

        return view('admin.families.create', compact(['users, filterUser ']));

    }


    public function edit(){

    }


    public function store(Request $request){

        $family_member_ids = $request->only('family_member_ids');

        $fam = new Family();

        $fam->created_by = auth()->user()->ID;

        foreach ($family_member_ids as $patient_id){

            $patient = PatientInfo::where('user_id', $patient_id)->first();

            if ($patient->family->count() < 1){

                $fam->users()->attach($patient);

            }

        }

    }


    public function delete(){

    }


}
