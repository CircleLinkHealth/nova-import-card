<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrolleeFamilyMemberService
{
    protected $enrolleeId;

    protected $enrollee;

    public function __construct($enrolleeId)
    {
        $this->enrolleeId = $enrolleeId;
    }

    public static function get($enrolleeId)
    {

        return (new static($enrolleeId))->generate();
    }

    private function generate()
    {
        $this->getModel();

        $query = $this->constructQuery();

        return $query->take(20)
                     ->get();
    }

    private function getModel()
    {
        $this->enrollee = Enrollee::findOrFail($this->enrolleeId);
    }

    private function constructQuery()
    {

        $phonesQuery = Enrollee::where('id', '!=', $this->enrolleeId)
                               ->searchPhones($this->enrollee->getPhonesE164AsString());

        $addressesQuery = Enrollee::where('id', '!=', $this->enrolleeId)
                                  ->searchAddresses($this->enrollee->getAddressesAsString());

        return $phonesQuery->union($addressesQuery);
    }

}