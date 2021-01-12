<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SharedModels\Entities\CarePlan;

class CareplanRepository
{
    private $G0506             = 'g0506';
    private $PATIENT_REJECTED  = 'patient_rejected';
    private $PROVIDER_APPROVED = 'provider_approved';

    public function approve($userId, $providerApproverId)
    {
        $carePlans = $this->model()->where(['user_id' => $userId]);

        if ($carePlans->first()) {
            $carePlans->update(['status' => $this->PROVIDER_APPROVED,
                'provider_approver_id'   => $providerApproverId,
                'provider_date'          => Carbon::now(),
            ]);

            return $carePlans->first();
        }
        throw new \Exception('careplans with user_id "'.$userId.'" not found');
    }

    public function model()
    {
        return app(CarePlan::class);
    }

    public function reject($userId, $providerApproverId = null)
    {
        $carePlans = $this->model()->where(['user_id' => $userId]);

        if ($carePlans->first()) {
            $carePlans->update(['status' => $this->G0506, 'provider_date' => Carbon::now()]);
            Patient::where(['user_id' => $userId])->update(['ccm_status' => $this->PATIENT_REJECTED]);
            if ($providerApproverId) {
                $carePlans->update(['provider_approver_id' => $providerApproverId]);
            }

            return $carePlans->first();
        }
        throw new \Exception('careplans with user_id "'.$userId.'" not found');
    }
}
