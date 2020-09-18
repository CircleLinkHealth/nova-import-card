<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\Customer\Entities\Family;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FamilyController extends Controller
{
    public function create()
    {
        $wpUsers = Patient::enrolled()->pluck('user_id');

        return view('admin.families.create', compact(['users, filterUser ']));
    }

    public function delete()
    {
    }

    public function edit()
    {
    }

    public function getMembers($patientId)
    {
        $members = [];
        /** @var Patient $patient */
        $patient = Patient::with(
            [
                'family.patients' => function ($q) {
                    $q->with([
                        'user' => function ($q) {
                            $q->without(['roles', 'perms'])
                                ->select(['id', 'display_name']);
                        },
                    ])->select(['id', 'user_id', 'family_id']);
                },
            ]
        )->whereUserId($patientId)->first();

        if ($patient && ! empty($patient->family)) {
            $members = $patient
                ->family
                ->patients
                ->reject(function (Patient $p) use ($patientId) {
                    // exclude current patient
                    return $p->user_id == $patientId;
                })
                ->map(function (Patient $p) {
                    return [
                        'user_id'      => $p->user->id,
                        'display_name' => $p->user->display_name,
                    ];
                })
                ->values();
        }

        return response()->json(['members' => $members]);
    }

    public function index()
    {
        $families = Family::all();

        return view('admin.families.index', compact(['families']));
    }

    public function store(Request $request)
    {
        $family_member_ids = explode(',', $request->input('family_member_ids'));

        $fam = new Family();

        $fam->created_by = auth()->user()->id;

        $fam->save();

        foreach ($family_member_ids as $patient_id) {
            $patient     = Patient::where('user_id', trim($patient_id))->first();
            $contact_clh = 'Please contact CLH Support for Manual Edits.';

            if ( ! is_object($patient)) {
                return "Sorry, {$patient_id} is not a patient in the system. ".$contact_clh;
            }

            if ($patient->family()->count() >= 1) {
                $fam->delete();

                return "Sorry, {$patient->user->getFullName()} already belongs to a family.<br> <br>".$contact_clh;
            }

            $patient->family_id = $fam->id;
            $patient->save();
        }

        return redirect()->back()->with(['message' => 'Created A Happy Family!']);
    }
}
