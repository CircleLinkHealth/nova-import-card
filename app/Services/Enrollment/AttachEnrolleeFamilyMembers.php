<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use App\SafeRequest;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class AttachEnrolleeFamilyMembers extends EnrolleeFamilyMembersService
{
    public static function attach(SafeRequest $request)
    {
        if ( ! $request->has('confirmed_family_members') || ! $request->has('enrollable_id')) {
            return false;
        }

        return (new static($request->input('enrollable_id')))->attachFamilyMembers($request);
    }

    private function attachFamilyMembers(SafeRequest $request)
    {
        $this->getModel();

        $ids = $request->input('confirmed_family_members');

        if (empty($ids)) {
            return false;
        }

        if ( ! is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $this->assignToCareAmbassador($request, $ids);

        //make sure to check if duplicate entry exists,
        //also attach the inverse for each on
        $this->enrollee->attachFamilyMembers($ids);

        $this->attachInverseRelationship($ids);
    }

    private function assignToCareAmbassador(SafeRequest $request, $ids)
    {
        //per CPM-2256 make sure to update confirmed family member statuses, to be able to pre-fill their data on CA-panel.
        //pre-fill status as well. Enrollee should still come next in queue, since we're not checking for status on Confirmed FamilyMembers Queue
        Enrollee::whereIn('id', $ids)->update([
            'care_ambassador_user_id'  => auth()->user()->id,
            'status'                   => $request->input('status') ?? Enrollee::TO_CALL,
            'last_call_outcome'        => $request->input('reason'),
            'last_call_outcome_reason' => $request->input('reason_other'),
            'other_note'               => $request->input('extra'),
            'preferred_window'         => $request->input('times') ? createTimeRangeFromEarliestAndLatest($request->input('times')) : null,
            'preferred_days'           => is_array($request->input('days')) ? collect($request->input('days'))->reject(function ($d) {
                return $d == 'all';
            })->implode(', ') : null,
        ]);
    }

    private function attachInverseRelationship($ids)
    {
        Enrollee::whereIn('id', $ids)
                ->get()
                ->each(function (Enrollee $e) {
                    if ( ! $e->confirmedFamilyMembers()->where('id', $this->enrollee->id)->exists()) {
                        $e->attachFamilyMembers($this->enrollee->id);
                    }
                });
    }
}