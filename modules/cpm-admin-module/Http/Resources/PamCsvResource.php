<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Resources;

use CircleLinkHealth\Customer\Actions\PatientTimeAndCalls;
use CircleLinkHealth\Customer\DTO\PatientTimeAndCalls as PatientTimeAndCallsValueObject;
use Illuminate\Http\Resources\Json\JsonResource;

class PamCsvResource extends JsonResource
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
        /** @var PatientTimeAndCallsValueObject */
        $supplementaryViewDataForPatient = PatientTimeAndCalls::get([$this->patient_id])->first();

        return '"'.$this->id.'",'.
               '"'.$this->type.'",'.
               '"'.$this->nurse.'",'.
               '"'.$this->patient.'",'.
               '"'.$this->practice.'",'.
               '"'.$this->scheduled_date.'",'.
               '"'.$this->call_time_start.'",'.
               '"'.$this->call_time_end.'",'.
               '"'.$this->preferredCallDaysToString().'",'.
               '"'.$this->last_call.'",'.
               '"'.$this->formatTime($supplementaryViewDataForPatient->getCcmTotalTime()).'",'.
               '"'.$this->formatTime($supplementaryViewDataForPatient->getBhiTotalTime()).'",'.
               '"'.$this->formatTime($supplementaryViewDataForPatient->getPcmTotalTime()).'",'.
               '"'.$this->formatTime($supplementaryViewDataForPatient->getRpmTotalTime()).'",'.
               '"'.$this->formatTime($supplementaryViewDataForPatient->getRhcTotalTime()).'",'.
               '"'.(string) $supplementaryViewDataForPatient->getNoOfSuccessfulCalls().'",'.
               '"'.$this->billing_provider.'",'.
               '"'.$this->scheduler.'"';
    }

    private function formatTime($time)
    {
        $seconds = $time;
        $H       = floor($seconds / 3600);
        $i       = ($seconds / 60) % 60;
        $s       = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $H, $i, $s);
    }
}
