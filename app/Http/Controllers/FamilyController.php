<?php

namespace App\Http\Controllers;

use App\Family;
use App\Patient;
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

        $wpUsers = Patient::enrolled()->pluck('user_id');

        return view('admin.families.create', compact(['users, filterUser ']));
    }


    public function edit()
    {
    }

    public function store(Request $request)
    {

        $family_member_ids =  explode(',', $request->input('family_member_ids'));

        $fam = new Family();

        $fam->created_by = auth()->user()->id;

        $fam->save();

        foreach ($family_member_ids as $patient_id) {
            $patient = Patient::where('user_id', trim($patient_id))->first();
            $contact_rohan = "Please contact Rohan for Manual Edits.";

            if (!is_object($patient)) {
                return "Sorry, {$patient_id} is not a patient in the system. " . $contact_rohan;
            }

            if ($patient->family()->count() >= 1) {
                $fam->delete();
                return "Sorry, {$patient->user->getFullName()} already belongs to a family.<br> <br>" . $contact_rohan;
            };

            $patient->family_id = $fam->id;
            $patient->save();
        }

        return redirect()->back()->with(['message' => 'Created A Happy Family!']);
    }

    public function delete()
    {
    }
}
