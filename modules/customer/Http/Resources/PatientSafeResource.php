<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientSafeResource extends JsonResource
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
        $locationName = 'N/A';
        $locationId   = null;

        if ($location = optional($this->patientInfo)->location) {
            $locationName = $location->name;
            $locationId   = $location->id;
        }

        return [
            'id'                    => $this->id,
            'username'              => $this->username,
            'name'                  => $this->name() ?? $this->display_name,
            'address'               => $this->address,
            'city'                  => $this->city,
            'state'                 => $this->state,
            'specialty'             => '',
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
            'careplan'              => optional($this->carePlan)->safe(),
            'last_read'             => optional($this->observations->first())->obs_date,
            'phone'                 => $this->getPhone() ?? optional($this->phoneNumbers->first())->number,
            'created_at'            => optional($this->created_at)->format('c') ?? null,
            'updated_at'            => optional($this->updated_at)->format('c') ?? null,
        ];
    }
}
