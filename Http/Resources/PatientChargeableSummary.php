<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientChargeableSummary extends JsonResource
{
    public function toArray($request)
    {
        /** @var ChargeablePatientMonthlyTime $resource */
        $resource = $this->resource;

        return [
            'patient_id'         => $resource->patient_user_id,
            'total_time'         => $resource->total_time,
            'chargeable_service' => ChargeableServiceForTimeTracker::make($this),
        ];
    }
}
