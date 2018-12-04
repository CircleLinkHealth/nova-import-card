<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources\SAAS;

use Illuminate\Http\Resources\Json\Resource;

class PracticeChargeableServices extends Resource
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
            'id'          => $this->id,
            'code'        => $this->code,
            'description' => $this->description,
            'amount'      => $this->amount,
            'is_on'       => $this->is_on,
        ];
    }
}
