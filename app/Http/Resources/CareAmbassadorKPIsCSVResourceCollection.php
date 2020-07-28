<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CareAmbassadorKPIsCSVResourceCollection extends ResourceCollection
{
    protected $end;
    protected $start;

    public function setTimeRange($start, $end)
    {
        $this->start = $start;
        $this->end   = $end;

        return $this;
    }

    public function toArray($request)
    {
        return $this->collection->map(function (CareAmbassadorKPIsCSVResource $resource) use ($request) {
            return $resource->setTimeRange($this->start, $this->end)->toString($request);
        })->filter()->all();
    }
}
