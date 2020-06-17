<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use App\Services\Enrollment\PracticeKPIs;
use Illuminate\Http\Resources\Json\Resource;

class PracticeKPIsCSVResource extends Resource
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
        $stats = PracticeKPIs::get($this->resource, $this->start, $this->end);

        return '"'.str_replace(',', '', $stats['name']).'",'.
            '"'.$stats['unique_patients_called'].'",'.
            '"'.$stats['consented'].'",'.
            '"'.$stats['utc'].'",'.
            '"'.$stats['soft_declined'].'",'.
            '"'.$stats['hard_declined'].'",'.
            '"'.$stats['incomplete_3_attempts'].'",'.
            '"'.$stats['labor_hours'].'",'.
            '"'.$stats['conversion'].'",'.
            '"'.$stats['labor_rate'].'",'.
            '"'.$stats['total_cost'].'",'.
            '"'.$stats['acq_cost'].'"';
    }
}
