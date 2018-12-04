<?php

namespace App\Services\CCD;

use App\User;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 6/10/16
 * Time: 3:35 PM
 */
class CcdInsurancePolicyService
{
    public function checkPendingInsuranceApproval(User $patient)
    {
        $hasPolicies = $patient->ccdInsurancePolicies()->get();

        //patient has no policies, so no approval needed
        if ($hasPolicies->isEmpty()) {
            return false;
        }

        //check if the user has approved insurance policies
        $approvedInsurance = $patient->ccdInsurancePolicies()
            ->whereApproved(true)
            ->get();

        return $approvedInsurance->isEmpty();
    }
}
