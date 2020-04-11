<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Resources\Enrollable;
use App\Services\Enrollment\EnrollableCallQueue;
use App\Services\Enrollment\SuggestEnrolleeFamilyMembers;

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
            EnrollableCallQueue::getNext(
                auth()->user()->careAmbassador
            )
        );
    }
}
