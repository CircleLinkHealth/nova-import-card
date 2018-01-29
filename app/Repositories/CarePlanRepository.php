<?php

namespace App\Repositories;

use App\CarePlan;

class CareplanRepository
{
    private $TO_ENROLL = 'to_enroll';
    private $PROVIDER_APPROVED = 'provider_approved';
    private $PATIENT_REJECTED = 'patient_rejected';

    public function model()
    {
        return app(CarePlan::class);
    }

    public function approve($userId, $providerApproverId) {

        $carePlans = $this->model()->where(['user_id' => $userId]);

        if ($carePlans->first()) {
            $carePlans->update([ 'status' => $this->PROVIDER_APPROVED, 'provider_approver_id' => $providerApproverId ]);
            return $carePlans->first();
        }
        else {
            throw new Exception('careplans with user_id "'.$userId.'" not found');
        }
    }

    public function reject($userId, $providerApproverId = null) {

        $carePlans = $this->model()->where(['user_id' => $userId]);
        
        if ($carePlans->first()) {
            $carePlans->update([ 'status' => $this->PATIENT_REJECTED ]);   

            if ($providerApproverId) {
                $carePlans->update([ 'provider_approver_id' => $providerApproverId ]); 
            }
            return $carePlans->first();
        }
        else {
            throw new Exception('careplans with user_id "'.$userId.'" not found');
        }
    }
}