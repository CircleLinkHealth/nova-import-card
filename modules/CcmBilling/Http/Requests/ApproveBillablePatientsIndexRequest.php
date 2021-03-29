<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;

class ApproveBillablePatientsIndexRequest extends SaasAdminRequest
{
    public function authorize()
    {
        if (parent::authorize()) {
            return true;
        }

        /** @var User $user */
        $user = auth()->user();

        return $user->hasPermission(['patientSummary.read', 'patientProblem.read', 'chargeableService.read', 'practice.read']);
    }

    public function rules()
    {
        return [];
    }
}
