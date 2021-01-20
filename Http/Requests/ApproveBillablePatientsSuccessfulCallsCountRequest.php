<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;

class ApproveBillablePatientsSuccessfulCallsCountRequest extends SaasAdminRequest
{
    public function authorize()
    {
        if (parent::authorize()) {
            return true;
        }

        $practiceId = $this->input('practice_id');

        /** @var User $user */
        $user = auth()->user();

        return $user->hasPermissionForSite(['patientSummary.read'], $practiceId);
    }

    public function rules()
    {
        return [
            'practice_id' => 'required|exists:practices,id',
            'patient_ids' => 'required|array',
            'date'        => 'required|date_format:"F, Y"',
        ];
    }
}
