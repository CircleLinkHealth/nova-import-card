<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\CareAmbassadorLog;
use App\Services\Enrollment\EnrolleeCallQueue;
use App\Services\Enrollment\SuggestEnrolleeFamilyMembers;
use App\TrixField;

class EnrollmentCenterController extends ApiController
{
    public function getSuggestedFamilyMembers($enrolleeId)
    {
        return $this->json([
            'suggested_family_members' => SuggestEnrolleeFamilyMembers::get((int) $enrolleeId),
        ]);
    }

    public function show()
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollee = EnrolleeCallQueue::getNext($careAmbassador);

        //todo: deal with this later
//        if (null == $enrollee) {
//            //no calls available
//            return view('enrollment-ui.no-available-calls');
//        }

        return response([
            'status' => 200,
            'data'   => [
                'enrollee' => $enrollee,
                'report'   => CareAmbassadorLog::createOrGetLogs($careAmbassador->id),
                'script'   => TrixField::careAmbassador($enrollee->lang)->first(),
                'provider' => $enrollee->provider,
            ],
        ]);
    }
}
