<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientContactWindows extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'day_of_week'       => $this->day_of_week,
            'window_time_start' => $this->window_time_start,
            'window_time_end'   => $this->window_time_end,
        ];
    }
}
