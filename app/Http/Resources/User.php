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
            'id'                  => $this->id,
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
            'created_at'          => $this->created_at ? $this->created_at->format('c') : null,
            'updated_at'          => $this->updated_at ? $this->updated_at->format('c') : null,
            'deleted_at'          => $this->deleted_at ? $this->deleted_at->format('c') : null,
            'timezone'          => $this->created_at ? $this->created_at->format('T') : null,
            'billing_provider'  => BillingProvider::make($this->whenLoaded('billingProvider')),
            'notes'             => Note::collection($this->whenLoaded('notes')),
            'nurse_info'        => NurseInfo::make($this->whenLoaded('nurseInfo')),
            'patient_info'      => PatientInfo::make($this->whenLoaded('patientInfo')),
            'patient_summaries' => PatientMonthlySummary::collection($this->whenLoaded('patientSummaries')),
            'provider_info'     => ProviderInfo::make($this->whenLoaded('providerInfo')),
            'primary_practice'  => Practice::make($this->whenLoaded('primaryPractice')),
            'status'            => optional($this->carePlan)->status
        ];
    }
}
