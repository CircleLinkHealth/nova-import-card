<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SetPatientChargeableServicesResponse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'approved'            => $this->approved,
            'rejected'            => $this->rejected,
            'qa'                  => $this->qa,
            'chargeable_services' => $this->chargeable_services,
        ];
    }
}
