<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class User extends Resource
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
        return [
            'username'            => $this->username,
            'email'               => $this->email,
            'user_registered'     => $this->user_registered,
            'full_name'           => $this->fullName,
            'first_name'          => $this->first_name,
            'last_name'           => $this->last_name,
            'suffix'              => $this->suffix,
            'address'             => $this->address,
            'address2'            => $this->address2,
            'city'                => $this->city,
            'state'               => $this->state,
            'zip'                 => $this->zip,
            'timezone'            => $this->timezone,
            'primary_practice_id' => $this->program_id,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'deleted_at'          => $this->deleted_at,

            'billing_provider' => BillingProvider::make($this->whenLoaded('billingProvider')),
            'nurse_info'       => NurseInfo::make($this->whenLoaded('nurseInfo')),
            'patient_info'     => PatientInfo::make($this->whenLoaded('patientInfo')),
            'provider_info'    => ProviderInfo::make($this->whenLoaded('providerInfo')),
            'primary_practice' => Practice::make($this->whenLoaded('primaryPractice')),
        ];
    }
}
