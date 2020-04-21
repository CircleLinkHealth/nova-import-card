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
        if ( ! $request->has('confirmed_family_members') || ! $request->has('enrollee_id')) {
            return false;
        }

        return (new static($request->input('enrollee_id')))->attachFamilyMembers($request->input('confirmed_family_members'));
    }

    private function assignToCareAmbassador($ids)
    {
        if (empty($ids)) {
            return false;
        }
        if ( ! is_array($ids)) {
            $ids = explode(',', $ids);
        }
        Enrollee::whereIn('id', $ids)->update([
            'care_ambassador_user_id' => auth()->user()->id,
            'status'                  => Enrollee::TO_CALL,
        ]);
    }

    private function attachFamilyMembers($ids)
    {
        $this->getModel();

        $this->assignToCareAmbassador($ids);

        //make sure to check if duplicate entry exists,
        //also attach the inverse for each on
        $this->enrollee->attachFamilyMembers($ids);

        $this->attachInverseRelationship($ids);
    }

    private function attachInverseRelationship($ids)
    {
        if (empty($ids)) {
            return false;
        }
        if ( ! is_array($ids)) {
            $ids = explode(',', $ids);
        }

        Enrollee::whereIn('id', $ids)
            ->get()
            ->each(function (Enrollee $e) {
                if ( ! $e->confirmedFamilyMembers()->where('id', $this->enrollee->id)->exists()) {
                    $e->attachFamilyMembers($this->enrollee->id);
                }
            });
    }
}
