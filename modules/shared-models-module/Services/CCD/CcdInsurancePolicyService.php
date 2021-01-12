<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\CCD;

use CircleLinkHealth\Customer\Entities\User;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 6/10/16
 * Time: 3:35 PM.
 */
class CcdInsurancePolicyService
{
    public function checkPendingInsuranceApproval(User $patient)
    {
        $patient->loadMissing('ccdInsurancePolicies');
        $hasPolicies = $patient->ccdInsurancePolicies;

        //patient has no policies, so no approval needed
        if ($hasPolicies->isEmpty()) {
            return false;
        }

        //check if the user has approved insurance policies
        $approvedInsurance = $patient->ccdInsurancePolicies
            ->where('approved', true);

        return $approvedInsurance->isEmpty();
    }
}
