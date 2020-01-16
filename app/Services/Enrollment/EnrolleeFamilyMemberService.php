<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use App\Http\Requests\Request;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrolleeFamilyMemberService
{
    /**
     * @var integer
     */
    protected $enrolleeId;

    /**
     * @var Enrollee
     */
    protected $enrollee;

    public function __construct(integer $enrolleeId)
    {
        $this->enrolleeId = $enrolleeId;
    }

    public static function get(integer $enrolleeId)
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

    private function formatForView($family){
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

    public static function attach($enrolleeId, Request $request)
    {
        if (! $request->has('confirmed_family_members')) {
            return false;
        }

        return (new static($enrolleeId))->attachFamilyMembers($request);
    }

    private function attachFamilyMembers(Request $request){
        $this->enrollee->attachFamilyMembers($request->input('confirmed_family_members'));
    }

}