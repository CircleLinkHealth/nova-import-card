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

    private function generate()
    {
        $this->getModel();

        $query = $this->constructQuery();

        return $this->formatForView($query->take(20)
                                          ->get());
    }

    private function constructQuery()
    {
        $phonesQuery = Enrollee::where('id', '!=', $this->enrolleeId)
                               ->searchPhones($this->enrollee->getPhonesE164AsString());

        $addressesQuery = Enrollee::where('id', '!=', $this->enrolleeId)
                                  ->searchAddresses($this->enrollee->getAddressesAsString());

        return $phonesQuery->union($addressesQuery);
    }

    private function formatForView($family)
    {
        return $family->map(function (Enrollee $e) {
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
    }
}