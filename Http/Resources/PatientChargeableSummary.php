<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientChargeableSummary extends JsonResource
{
    public function toArray($request)
    {
        return [
            'patient_id'         => $this->patient_user_id,
            'total_time'         => $this->total_time,
            'chargeable_service' => ChargeableServiceForTimeTracker::make($this),
        ];
    }
}
