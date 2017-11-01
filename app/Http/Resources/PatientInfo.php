<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PatientInfo extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'monthly_summaries'     => PatientMonthlySummary::collection($this->whenLoaded('monthlySummaries')),
        ];
    }
}
