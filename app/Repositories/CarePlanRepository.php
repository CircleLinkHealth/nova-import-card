<?php

namespace App\Repositories;

use App\CarePlan;
use App\Patient;
use Carbon\Carbon;

class CareplanRepository
{
    private $PROVIDER_APPROVED = 'provider_approved';
    private $PATIENT_REJECTED = 'patient_rejected';
    private $G0506 = 'g0506';

    public function model()
    {
        return app(CarePlan::class);
    }

    public function approve($userId, $providerApproverId)
    {
        $carePlans = $this->model()->where(['user_id' => $userId]);

        if ($carePlans->first()) {
            $carePlans->update(['status'               => $this->PROVIDER_APPROVED,
                                'provider_approver_id' => $providerApproverId,
                                'provider_date'        => Carbon::now(),
            ]);

            return $carePlans->first();
        } else {
            throw new Exception('careplans with user_id "' . $userId . '" not found');
        }
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
        } else {
            throw new Exception('careplans with user_id "' . $userId . '" not found');
        }
    }
}
