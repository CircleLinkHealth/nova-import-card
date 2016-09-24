<?php

namespace App\Http\Controllers;

use App\Family;
use App\Http\Requests;
use App\PatientInfo;
use Illuminate\Http\Request;

class FamilyController extends Controller
{

    public function index()
    {

        $families = Family::all();

        return view('admin.families.index', compact(['families']));

    }

    public function create()
    {

        $wpUsers = PatientInfo::enrolled()->pluck('user_id');

        return view('admin.families.create', compact(['users, filterUser ']));

    }


    public function edit(){

    }

    public function store(Request $request){

        $family_member_ids =  explode(',', $request->input('family_member_ids'));


        $fam = new Family();

        $fam->created_by = auth()->user()->ID;

        $fam->save();

        foreach ($family_member_ids as $patient_id){

            $patient = PatientInfo::where('user_id', $patient_id)->first();

            if ($patient->family()->count() >= 1){

                $fam->delete();
                return "Sorry, {$patient->user->fullName} already belongs to a family. <br> <br> Please contact Rohan for manual edits.";

            };

            $patient->family_id = $fam->id;
            $patient->save();

        }

        return $fam;

    }

    public function delete(){

    }


}
