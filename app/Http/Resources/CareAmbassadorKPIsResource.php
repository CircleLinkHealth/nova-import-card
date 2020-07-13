<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use App\Services\Enrollment\CareAmbassadorKPIs;
use Illuminate\Http\Resources\Json\JsonResource;

class CareAmbassadorKPIsResource extends JsonResource
{
    protected $end;
    protected $start;

    public static function collection($resource)
    {
        return new PracticeKPIsCSVResourceCollection($resource);
    }

    public function setTimeRange($start, $end)
    {
        $this->start = $start;
        $this->end   = $end;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return string
     */
    public function toArray($request)
    {
        $stats = CareAmbassadorKPIs::get($this->resource, $this->start, $this->end);

        return '"'.str_replace(',', '', $stats['name']).'",'.
            '"'.$stats['total_hours'].'",'.
            '"'.$stats['total_seconds'].'",'.
            '"'.$stats['no_enrolled'].'",'.
            '"'.$stats['total_calls'].'",'.
            '"'.$stats['calls_per_hour'].'",'.
            '"'.$stats['mins_per_enrollment'].'",'.
            '"'.$stats['conversion'].'",'.
            '"'.$stats['hourly_rate'].'",'.
            '"'.$stats['per_cost'].'"';
    }
}
