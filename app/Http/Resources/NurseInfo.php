<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class NurseInfo extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $nurse = parent::toArray($request);
        if (array_key_exists('states', $nurse) && $request->has('compressed')) {
            $nurse['states'] = (new Collection($nurse['states']))->map(function ($s) {
                return $s['code'];
            });
        }
        return $nurse;
    }
}
