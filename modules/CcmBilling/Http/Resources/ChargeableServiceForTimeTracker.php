<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeableServiceForTimeTracker extends JsonResource
{
    public function toArray($request)
    {
        /** @var ChargeablePatientMonthlyTime $resource */
        $resource = $this->resource;

        return [
            'id'           => $resource->chargeable_service_id,
            'code'         => $resource->chargeableService->code,
            'display_name' => $resource->chargeableService->display_name,
        ];
    }
}
