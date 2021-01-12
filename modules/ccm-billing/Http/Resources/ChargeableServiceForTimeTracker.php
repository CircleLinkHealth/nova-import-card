<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChargeableServiceForTimeTracker extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->chargeable_service_id,
            'code'         => $this->chargeable_service_code,
            'display_name' => $this->chargeable_service_name,
        ];
    }
}
