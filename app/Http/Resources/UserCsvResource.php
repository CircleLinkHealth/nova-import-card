<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;

class UserCsvResource extends Resource
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
        $practice = optional($this->primaryPractice()->first());
        $patient  = optional($this->patientInfo()->first());
        $careplan = optional($this->carePlan()->first());
        $ccmStatusDate = '';
        if ($patient->ccm_status == 'paused'){
            $ccmStatusDate = $patient->date_paused;
        }
        if ($patient->ccm_status == 'withdrawn'){
            $ccmStatusDate = $patient->date_withdrawn;
        }
        if ($patient->ccm_status == 'unreachable'){
            $ccmStatusDate = $patient->date_unreachable;
        }

        return ('"' . $this->display_name ?? $this->name()) . '",' .
               '"' . $this->billing_provider_name . '",' .
               '"' . $practice->display_name . '",' .
               '"' . $patient->ccm_status . '",' .
               '"' . $ccmStatusDate . '",' .
               '"' . $careplan->status . '",' .
               '"' . $patient->birth_date . '",' .
               '"' . $this->phone . '",' .
               '"' . ($patient->birth_date
                ? Carbon::parse($patient->birth_date)->age
                : 0) . '",' .
               '"' . $this->created_at . '",' .
               '"' . ($patient->cur_month_activity_time
                ? gmdate('i:s', $patient->cur_month_activity_time)
                : '00:00') . '"';
    }
}
