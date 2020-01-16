<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Services\Enrollment\EnrolleeFamilyMemberService;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrollmentCenterController extends ApiController
{
    public function getSuggestedFamilyMembers($enrolleeId)
    {
        $suggestedFamilyMembers = EnrolleeFamilyMemberService::get($enrolleeId)
                                                             ->map(function (Enrollee $e) {
                                                                 return [
                                                                     'id'         => $e->id,
                                                                     'first_name' => $e->first_name,
                                                                     'last_name'  => $e->last_name,
                                                                     'phones'     => [
                                                                         'value' => $e->getPhonesAsString(),
                                                                     ],
                                                                     'addresses'  => [
                                                                         'value' => $e->getAddressesAsString(),
                                                                     ],
                                                                 ];
                                                             });

        return $this->json([
            'suggested_family_members' => $suggestedFamilyMembers,
        ]);
    }
}
