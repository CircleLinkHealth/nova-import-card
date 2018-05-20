<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserSafeResource extends Resource
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
        $careplan    = $this->carePlan()->first();
        $observation = $this->observations()->orderBy('id', 'desc')->first();
        $phone       = $this->phoneNumbers()->first();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name() ?? $this->display_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'specialty' => $this->specialty,
            'program_id' => $this->program_id,
            'status' => $this->status,
            'user_status' => $this->user_status,
            'is_online' => $this->is_online,
            'patient_info' => optional($this->patientInfo()->first())->safe(),
            'provider_info' => $this->providerInfo()->first(),
            'billing_provider_name' => $this->billing_provider_name,
            'billing_provider_id' => $this->billing_provider_id,
            'careplan' => optional($careplan)->safe(),
            'last_read' => optional($observation)->obs_date,
            'phone' => $this->phone ?? optional($phone)->number,
            'created_at' => optional($this->created_at)->format('c') ?? null,
            'updated_at' => optional($this->updated_at)->format('c') ?? null
        ];
    }
}
