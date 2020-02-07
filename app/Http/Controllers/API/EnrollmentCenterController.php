<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Services\Enrollment\SuggestEnrolleeFamilyMembers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrollmentCenterController extends ApiController
{
    public function getSuggestedFamilyMembers($enrolleeId)
    {
        return $this->json([
            'suggested_family_members' => SuggestEnrolleeFamilyMembers::get((int)$enrolleeId),
        ]);
    }
}
