<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientServiceForTimeTrackerDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientChargeableSummary extends JsonResource
{
    public function toArray($request)
    {
        /** @var PatientServiceForTimeTrackerDTO $resource */
        $resource = $this->resource;

        return [
            'patient_id'         => $resource->getPatientId(),
            'total_time'         => $resource->getTotalTime(),
            'chargeable_service' => ChargeableServiceForTimeTracker::make($this),
        ];
    }
}
