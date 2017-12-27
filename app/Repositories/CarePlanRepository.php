<?php

namespace App\Repositories;

use App\CarePlan;

class CareplanRepository
{
    private $TO_ENROLL = 'to_enroll';
    private $PROVIDER_APPROVED = 'provider_approved';

    public function model()
    {
        return app(CarePlan::class);
    }

    public function approve($userId, $providerApproverId) {

        $carePlans = $this->model()->where(['user_id' => $userId]);

        if ($carePlans) {
            $carePlans->update([ 'status' => $this->PROVIDER_APPROVED, 'provider_approver_id' => $providerApproverId ]);   
        }
        else {
            throw new Exception('careplans with user_id "'.$userId.'" not found');
        }
    }
}