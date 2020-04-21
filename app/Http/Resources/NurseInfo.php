<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class NurseInfo extends JsonResource
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
        $nurse = parent::toArray($request);
        if (array_key_exists('states', $nurse) && $request->has('compressed')) {
            $nurse['states'] = (new Collection($nurse['states']))->map(function ($s) {
                return $s['code'];
            });
            $this->loadMissing('user.practices');
            if ($this->user) {
                $nurse['practices'] = $this->user->practices->pluck('id');
            }
        }

        return $nurse;
    }
}
