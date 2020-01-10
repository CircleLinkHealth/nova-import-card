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
        $enrollee = Enrollee::find($enrolleeId);

//        $query = Enrollee::
//        where('id', '!=', $enrolleeId);

        $columns = implode(',', ['address', 'address_2']);

        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term            = str_replace($reservedSymbols, '', $enrollee->address.' '.$enrollee->address_2);

        $words = explode(' ', $term);

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 3) {
                $words[$key] = $word.'*';
            }
        }

        $searchTerm = implode(' ', $words);

        $query = Enrollee::whereRaw("MATCH ({$columns}) AGAINST (?)", $searchTerm);

        $suggestedFamilyMembers = $query
            ->get()
            ->map(function ($e) {
                return [
                    'id'         => $e->id,
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
