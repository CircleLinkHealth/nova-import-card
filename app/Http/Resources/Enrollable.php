<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;


use App\CareAmbassadorLog;
use App\TrixField;
use Illuminate\Http\Resources\Json\Resource;

class Enrollable extends Resource
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
        $careAmbassador = $this->careAmbassador->careAmbassador;

        return [
            'enrollee' => $this->resource->toArray(),
            'report'   => CareAmbassadorLog::createOrGetLogs($careAmbassador->id),
            'script'   => TrixField::careAmbassador($this->lang)->first(),
            'provider' => $this->provider->toArray(),
            'providerPhone' => $this->provider->getPhone(),
            'hasTips' => !! $this->practice->enrollmentTips
        ];

    }
}