<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;

class JsonMedicalRecordEligibilityJobToCsvAdapter
{
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityJob
     */
    private $job;

    public function __construct(EligibilityJob $job)
    {
        $this->job = $job;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $adapted = (new JsonMedicalRecordInsurancePlansAdapter())->adapt($this->job->data);

        return [
            'eligibility_job_id' => $this->job->id,
            'outcome'            => $this->job->outcome,
            'reason'             => $this->job->reason,

            'messages' => json_encode($this->job->messages),

            'patient_id'    => $this->job->data['patient_id'],
            'first_name'    => $this->job->data['first_name'],
            'middle_name'   => $this->job->data['middle_name'],
            'last_name'     => $this->job->data['last_name'],
            'date_of_birth' => $this->job->data['date_of_birth'],

            'preferred_provider' => $this->job->data['preferred_provider'],

            'primary_insurance'   => $adapted['primary_insurance'] ?? '',
            'secondary_insurance' => $adapted['secondary_insurance'] ?? '',
            'tertiary_insurance'  => $adapted['tertiary_insurance'] ?? '',

            'last_visit' => $this->job->data['last_visit'],

            'primary_phone' => $this->job->data['primary_phone'],
            'cell_phone'    => $this->job->data['cell_phone'],

            'address_line_1' => $this->job->data['address_line_1'],
            'address_line_2' => $this->job->data['address_line_2'],
            'city'           => $this->job->data['city'],
            'state'          => $this->job->data['state'],
            'postal_code'    => $this->job->data['postal_code'],

            //@todo: make adapters for below if needed
            //                    'problems'           => $this->job->data['problems'],
            //                    'medications'        => $this->job->data['medications'],
            //                    'allergies'          => $this->job->data['allergies'],
        ];
    }
}
