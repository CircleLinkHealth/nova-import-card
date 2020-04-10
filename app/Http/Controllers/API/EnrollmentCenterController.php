<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\CareAmbassadorLog;
use App\Http\Resources\Enrollable;
use App\Services\Enrollment\EnrolleeCallQueue;
use App\Services\Enrollment\SuggestEnrolleeFamilyMembers;
use App\TrixField;

class EnrollmentCenterController extends ApiController
{
    public function getSuggestedFamilyMembers($enrolleeId)
    {
        return $this->json([
            'suggested_family_members' => SuggestEnrolleeFamilyMembers::get((int)$enrolleeId),
        ]);
    }

    public function show()
    {
        return Enrollable::make(
            EnrolleeCallQueue::getNext(
                auth()->user()->careAmbassador
            )
        );
    }
}
