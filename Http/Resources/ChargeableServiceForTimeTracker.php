<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\CcmBilling\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChargeableServiceForTimeTracker extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'display_name' => $this->display_name,
        ];
    }
}
