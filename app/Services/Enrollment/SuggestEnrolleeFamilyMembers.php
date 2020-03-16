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

    private function constructQuery()
    {
        $phonesQuery = Enrollee::shouldSuggestAsFamilyForEnrollee($this->enrolleeId)
            ->searchPhones($this->enrollee->getPhonesE164AsString());

        $addressesQuery = Enrollee::shouldSuggestAsFamilyForEnrollee($this->enrolleeId)
            ->searchAddresses($this->enrollee->getAddressesAsString());

        return $phonesQuery->union($addressesQuery);
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
                    'value' => $e->getPhonesAsString(),
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

        $query = $this->constructQuery();

        return $this->formatForView($query->take(10)
            ->get()->unique('id'));
    }
}
