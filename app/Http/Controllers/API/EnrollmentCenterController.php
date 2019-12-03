<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Enrollee;

class EnrollmentCenterController extends ApiController
{
    public function getSuggestedFamilyMembers($enrolleeId)
    {
        $suggestedFamilyMembers = Enrollee::where('id', '!=', $enrolleeId)
            ->take(4)
            ->get()
            ->map(function ($e) {
                                              return [
                                                  'first_name' => $e->first_name,
                                                  'last_name'  => $e->last_name,
                                                  'phone'      => [
                                                      'value'       => $e->primary_phone,
                                                      'is_matching' => 1,
                                                  ],
                                                  'address' => [
                                                      'value'       => $e->address,
                                                      'is_matching' => 1,
                                                  ],
                                              ];
                                          });

        return $this->json([
            'suggested_family_members' => $suggestedFamilyMembers,
        ]);
    }
}
