<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PatientContactWindows extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'day_of_week'       => 1,
            'window_time_start' => '09:00:00',
            'window_time_end'   => '17:00:00',
        ];
    }
}
