<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmSymptomUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SymptomsController extends Controller
{
    public function destroy($userId, $symptomId)
    {
        if ($userId && $symptomId) {
            $result = CpmSymptomUser::where([
                'patient_id'     => $userId,
                'cpm_symptom_id' => $symptomId,
            ])->delete();

            return $result ? response()->json($result) : \response('provided patient does not have the symptom in question');
        }

        return \response('"symptomId" and "userId" are important');
    }

    public function show($userId)
    {
        if ($userId) {
            return User::with('cpmSymptoms')->firstOrFail()->cpmSymptoms;
        }

        return \response('"userId" is important');
    }

    public function store($userId, Request $request)
    {
        $symptomId = $request->input('symptomId');
        if ($userId && $symptomId) {
            return CpmSymptomUser::firstOrCreate(['cpm_symptom_id' => $symptomId, 'patient_id' => $userId]);
        }

        return \response('"symptomId" and "userId" are important');
    }
}
