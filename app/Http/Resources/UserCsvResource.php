<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;

class UserCsvResource extends Resource
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
        $practice      = $this->primaryPractice;
        $patient       = $this->patientInfo;
        $careplan      = $this->carePlan;
        $ccmStatusDate = '';
        if ('paused' == $patient->ccm_status) {
            $ccmStatusDate = $patient->date_paused;
        }
        if ('withdrawn' == $patient->ccm_status) {
            $ccmStatusDate = $patient->date_withdrawn;
        }
        if ('unreachable' == $patient->ccm_status) {
            $ccmStatusDate = $patient->date_unreachable;
        }

        return ('"'.$this->display_name ?? $this->name()).'",'.
               '"'.$this->getBillingProviderName().'",'.
               '"'.$practice->display_name.'",'.
               '"'.$patient->ccm_status.'",'.
               '"'.$careplan->status.'",'.
               '"'.$patient->birth_date.'",'.
               '"'.$this->getPhone().'",'.
               '"'.($patient->birth_date
                ? Carbon::parse($patient->birth_date)->age
                : 0).'",'.
               '"'.$this->created_at.'",'.
               '"'.$this->getTimeInDecimals($this->getCcmTime()).'",'.
               '"'.$ccmStatusDate.'"';
    }

    /**
     * Get CCM time in minutes (decimal form) from seconds.
     *
     * @param string|null $ccmTime in seconds
     *
     * @return string CCM minutes in decimal
     */
    private function getTimeInDecimals(String $ccmTime = null)
    {
        if (!$ccmTime) {
            return '0.00';
        }

        return number_format($ccmTime / 60, 2, '.', '');
    }
}
