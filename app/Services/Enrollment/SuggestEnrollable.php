<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\SharedModels\Entities\Enrollee;

class SuggestEnrollable extends EnrollableService
{
    const NINETY_PERCENT = 90;

    public static function get($enrolleeId)
    {
        return (new static($enrolleeId))->generate();
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
                    'value' => $e->getAddressesAsString($this->enrollee),
                ],
            ];
        });
    }

    private function generate()
    {
        $this->getModel();

        $results = $this->getSuggestions();

        \Log::info("Suggested family members for enrollee: {$this->enrollee->id}, results: {$results->pluck('id')->implode(',')}");

        return $this->formatForView($results);
    }

    private function getSuggestions()
    {
        //Exact phone number match is the best indicator for a family member
        $matchingPhones = Enrollee::shouldSuggestAsFamilyForEnrollee($this->enrollee)
            ->searchPhones($this->enrollee->getPhonesE164AsString())->get();

        $matchingAddressAndSurname = Enrollee::shouldSuggestAsFamilyForEnrollee($this->enrollee)
            ->searchAddresses($this->enrollee->getAddressesAsString())
            ->get()
            ->map(function (Enrollee $e) {
                //If there is a similar address but not the same phone number, we can take into account if the person with a similar address has the same last name.
                // That is another indicator our care ambassadors use to determine if someone is related.
                if ($e->relevance_score < (int) suggestedFamilyMemberAcceptableRelevanceScore()) {
                    return null;
                }

                if ( ! $this->levenshteinValidAddressExists($e)) {
                    return null;
                }

                if (levenshtein($e->last_name, $this->enrollee->last_name) > 1) {
                    return null;
                }

                return $e;
            })
            ->filter();

        return $matchingPhones->merge($matchingAddressAndSurname)->unique('id');
    }

    private function levenshteinValidAddressExists(Enrollee $e)
    {
        $levenshteinDistances          = collect([]);
        $enrolleeAddresses             = collect([$this->enrollee->address, $this->enrollee->address_2])->filter();
        $possibleFamilyMemberAddresses = collect([$e->address, $e->address_2])->filter();

        foreach ($enrolleeAddresses as $address) {
            foreach ($possibleFamilyMemberAddresses as $possibleMatchingAddress) {
                $levenshteinDistances->push(levenshteinPercent($address, $possibleMatchingAddress));
            }
        }

        $levenshteinValidAddresses = $levenshteinDistances->filter(function ($l) {
            //Levenshtein Distance transformed to relevance percent
            return $l >= self::NINETY_PERCENT;
        });

        return 0 !== $levenshteinValidAddresses->count();
    }
}
