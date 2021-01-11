<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Enrollable extends JsonResource
{
    public function toArray($request)
    {
        return $this->resource;
    }
}
