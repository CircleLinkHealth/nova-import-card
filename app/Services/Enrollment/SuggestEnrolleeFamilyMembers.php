<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\Eligibility\Entities\Enrollee;

class SuggestEnrolleeFamilyMembers extends EnrolleeFamilyMembersService
{
    public static function get($enrolleeId)
    {
        return (new static($enrolleeId))->generate();
    }

    private function getSuggestions()
    {
        //Exact phone number match is the best indicator for a family member
        $matchingPhones = Enrollee::shouldSuggestAsFamilyForEnrollee($this->enrolleeId)
            ->searchPhones($this->enrollee->getPhonesE164AsString())->get();

        $matchingAddressAndSurname = Enrollee::shouldSuggestAsFamilyForEnrollee($this->enrolleeId)
            ->searchAddresses($this->enrollee->getAddressesAsString())
        ->get()
        ->map(function ($e){
            //If there is a similar address but not the same phone number, we can take into account if the person with a similar address has the same last name.
            // That is another indicator our care ambassadors use to determine if someone is related.
            if ($e->relevance_score < (int)suggestedFamilyMemberAcceptableRelevanceScore()){
                return null;
            }

            if ($e->last_name !== $this->enrollee->last_name){
                return null;
            }

            return $e;
        })
        ->filter();

        return $matchingPhones->merge($matchingAddressAndSurname)->unique('id');
    }

    private function formatForView($family)
    {
        return $family->map(function (Enrollee $e) {
            return [
                'id'           => $e->id,
                'first_name'   => $e->first_name,
                'last_name'    => $e->last_name,
                'is_confirmed' => $this->enrollee->confirmedFamilyMembers->contains('id', $e->id),
                'phones'       => [
                    'value' => $e->getPhonesAsString($this->enrollee),
                ],
                'addresses' => [
                    'value' => $e->getAddressesAsString(),
                ],
            ];
        });
    }

    private function generate()
    {
        $this->getModel();

        $results = $this->getSuggestions();

        return $this->formatForView($results);
    }
}
