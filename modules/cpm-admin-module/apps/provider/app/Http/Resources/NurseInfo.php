<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NurseInfo extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $nurse = parent::toArray($request);
        if (isset($nurse['states']) && $request->has('compressed')) {
            $nurse['states'] = collect($nurse['states'])->map(function ($s) {
                return $s['code'];
            });
            if ($this->user) {
                $nurse['practices'] = $this->user->practices->pluck('id');
            }
        }

        return $nurse;
    }
}
