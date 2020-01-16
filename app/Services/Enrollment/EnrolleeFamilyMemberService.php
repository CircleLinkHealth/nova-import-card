<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use App\Http\Requests\Request;
use App\SafeRequest;
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

    public static function attach(SafeRequest $request)
    {
        if ( ! $request->has('confirmed_family_members') || ! $request->has('enrollee_id')) {
            return false;
        }


        return (new static($request->input('enrollee_id')))->attachFamilyMembers($request->input('confirmed_family_members'));
    }

    private function attachFamilyMembers($ids)
    {
        $this->getModel();

        $this->assignToCareAmbassador($ids);

        $this->enrollee->attachFamilyMembers($ids);
    }

    private function assignToCareAmbassador($ids){

        if (empty($ids)) {
            return false;
        }
        if ( ! is_array($ids)) {
            $ids = explode(',', $ids);
        }
        Enrollee::whereIn('id', $ids)->update([
            'care_ambassador_user_id' => auth()->user()->id,
            'status'             => Enrollee::TO_CALL,
        ]);
    }

}