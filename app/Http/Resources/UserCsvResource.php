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

        return ('"' . $this->display_name ?? $this->name()) . '",' .
               '"' . $this->billing_provider_name . '",' .
               '"' . $practice->display_name . '",' .
               '"' . $patient->ccm_status . '",' .
               '"' . $careplan->status . '",' .
               '"' . $patient->birth_date . '",' .
               '"' . $this->phone . '",' .
               '"' . ($patient->birth_date
                ? Carbon::parse($patient->birth_date)->age
                : 0) . '",' .
               '"' . $this->created_at . '",' .
               '"' . $this->getTimeInDecimals($patient->cur_month_activity_time) . '"';
    }

    /**
     * Get CCM time in minutes (decimal form) from seconds.
     *
     * @param String|null $ccmTime in seconds
     *
     * @return string CCM minutes in decimal
     */
    private function getTimeInDecimals(String $ccmTime = null) {
        if (!$ccmTime) {
            return '0.00';
        }
        return number_format($ccmTime / 60, 2, '.', '') ;
    }
}
