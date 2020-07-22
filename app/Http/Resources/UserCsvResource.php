<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCsvResource extends JsonResource
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
        $practice = $this->primaryPractice;

        if ( ! $practice) {
            \Log::critical("Patient with id:{$this->id} does not have Practice attached.");
        }

        /** @var Patient $patientInfo */
        $patient  = $this->patientInfo;
        $careplan = $this->carePlan;

        $locationName = 'n/a';
        if ( ! is_null($patient) && $patient->relationLoaded('location') && $patient->location) {
            $locationName = $patient->location->name;
        }

        return ('"'.$this->display_name ?? $this->name()).'",'.
               '"'.$this->getBillingProviderName().'",'.
               '"'.optional($practice)->display_name.'",'.
               '"'.$locationName.'",'.
               '"'.$patient->ccm_status.'",'.
               '"'.optional($careplan)->status.'",'.
               '"'.$patient->withdrawn_reason.'",'.
               '"'.$patient->birth_date.'",'.
               '"'.$patient->mrn_number.'",'.
               '"'.$this->getPhone().'",'.
               '"'.($patient->birth_date
                ? Carbon::parse($patient->birth_date)->age
                : 0).'",'.
               '"'.$this->created_at.'",'.
               '"'.$this->getTimeInDecimals($this->getBhiTime()).'",'.
               '"'.$this->getTimeInDecimals($this->getCcmTime()).'",'.
               '"'.$this->getPatientCcmStatusDate($patient).'"';
    }

    private function getPatientCcmStatusDate(Patient $patient)
    {
        $ccmStatus = $patient->ccm_status;

        switch ($ccmStatus) {
            case Patient::PAUSED:
                return $patient->date_paused;
            case in_array($patient->ccm_status, [
                Patient::WITHDRAWN_1ST_CALL,
                Patient::WITHDRAWN,
            ]):
                return $patient->date_withdrawn;
            case Patient::UNREACHABLE:
                return $patient->date_unreachable;
            default:
                return '';
        }
    }

    /**
     * Get CCM time in minutes (decimal form) from seconds.
     *
     * @param string|null $ccmTime in seconds
     *
     * @return string CCM minutes in decimal
     */
    private function getTimeInDecimals(string $ccmTime = null)
    {
        if ( ! $ccmTime) {
            return '0.00';
        }

        return number_format($ccmTime / 60, 2, '.', '');
    }
}
