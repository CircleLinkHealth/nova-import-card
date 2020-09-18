<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Resources;

use App\Http\Resources\BillingProvider;
use App\Http\Resources\Note;
use App\Http\Resources\NurseInfo;
use App\Http\Resources\PatientInfo;
use App\Http\Resources\PatientMonthlySummary;
use App\Http\Resources\PracticeResource;
use App\Http\Resources\ProviderInfo;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
        $result = [
            'id'                  => $this->id,
            'username'            => $this->username,
            'email'               => $this->email,
            'user_registered'     => $this->user_registered,
            'full_name'           => $this->getFullName(),
            'first_name'          => $this->getFirstName(),
            'last_name'           => $this->getLastName(),
            'suffix'              => $this->getSuffix(),
            'address'             => $this->address,
            'address2'            => $this->address2,
            'city'                => $this->city,
            'state'               => $this->state,
            'zip'                 => $this->zip,
            'primary_practice_id' => $this->program_id,
            'created_at'          => $this->created_at
                ? $this->created_at->format('c')
                : null,
            'updated_at' => $this->updated_at
                ? $this->updated_at->format('c')
                : null,
            'deleted_at' => $this->deleted_at
                ? $this->deleted_at->format('c')
                : null,
            'timezone' => $this->timezone
                ? \Carbon::now()->setTimezone($this->timezone)->format('T')
                : null,
            'billing_provider'  => BillingProvider::make($this->whenLoaded('billingProvider')),
            'notes'             => Note::collection($this->whenLoaded('notes')),
            'nurse_info'        => NurseInfo::make($this->whenLoaded('nurseInfo')),
            'patient_info'      => PatientInfo::make($this->whenLoaded('patientInfo')),
            'patient_summaries' => PatientMonthlySummary::collection($this->whenLoaded('patientSummaries')),
            'provider_info'     => ProviderInfo::make($this->whenLoaded('providerInfo')),
            'primary_practice'  => PracticeResource::make($this->whenLoaded('primaryPractice')),
            'status'            => optional($this->carePlan)->status,
            'is_bhi'            => $this->isBhi(),
            'is_ccm'            => $this->isCcm(),
        ];

        if ($this->relationLoaded('patientSummaries')) {
            $result['ccm_time'] = $this->getCcmTime();
            $result['bhi_time'] = $this->getBhiTime();
        }

        return $result;
    }
}
