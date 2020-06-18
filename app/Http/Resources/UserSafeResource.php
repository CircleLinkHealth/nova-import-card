<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSafeResource extends JsonResource
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
        $careplan     = $this->carePlan;
        $observation  = $this->observations->first();
        $phone        = $this->phoneNumbers->first();
        $locationName = 'N/A';
        $locationId   = null;

        /** @var Patient $patientInfo */
        $patientInfo = $this->whenLoaded('patientInfo');
        if ( ! is_null($patientInfo) && $patientInfo->relationLoaded('location') && ! is_null($patientInfo->location)) {
            $locationName = $patientInfo->location->name;
            $locationId   = $patientInfo->location->id;
        }

        return [
            'id'                    => $this->id,
            'username'              => $this->username,
            'name'                  => $this->name() ?? $this->display_name,
            'address'               => $this->address,
            'city'                  => $this->city,
            'state'                 => $this->state,
            'specialty'             => $this->getSpecialty(),
            'program_id'            => $this->program_id,
            'status'                => $this->status,
            'user_status'           => $this->user_status,
            'is_online'             => $this->is_online,
            'patient_info'          => optional($this->patientInfo)->safe(),
            'provider_info'         => $this->providerInfo,
            'billing_provider_name' => $this->getBillingProviderName(),
            'billing_provider_id'   => $this->getBillingProviderId(),
            'location_name'         => $locationName,
            'location_id'           => $locationId,
            'careplan'              => optional($careplan)->safe(),
            'last_read'             => optional($observation)->obs_date,
            'phone'                 => $this->getPhone() ?? optional($phone)->number,
            'ccm_time'              => $this->getCcmTime(),
            'bhi_time'              => $this->getBhiTime(),
            'created_at'            => optional($this->created_at)->format('c') ?? null,
            'updated_at'            => optional($this->updated_at)->format('c') ?? null,
        ];
    }
}
