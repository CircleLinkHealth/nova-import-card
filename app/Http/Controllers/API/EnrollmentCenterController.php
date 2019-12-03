<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */
namespace App\Http\Controllers\API;


use App\Enrollee;

class EnrollmentCenterController extends ApiController
{
    public function getSuggestedFamilyMembers($enrolleeId){
        //get enrollees - create class that does all these
        //format data

        $suggestedFamilyMembers = Enrollee::where('id', '!=', $enrolleeId)
                                          ->take(4)
                                        ->get()
                                          ->map(function ($e){
            return [
                'first_name' => $e->first_name,
                'last_name' => $e->last_name,
                'phones' => [
                    $e->primary_phone,
                    $e->other_phone,
                    $e->home_phone,
                    $e->cell_phone
                ],
                'addresses' => [
                    $e->adress,
                    $e->address_2
                ]
            ];
        });

        return $this->json([
            'status' => 200,
            'suggested_family_members' => $suggestedFamilyMembers
        ]);
    }
}