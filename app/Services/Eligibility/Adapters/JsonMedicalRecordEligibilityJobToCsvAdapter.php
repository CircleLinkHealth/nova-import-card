<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 10/12/18
 * Time: 1:41 PM
 */

namespace App\Services\Eligibility\Adapters;


use App\EligibilityJob;

class JsonMedicalRecordEligibilityJobToCsvAdapter
{
    /**
     * @var EligibilityJob
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
            'outcome'  => $this->job->outcome,
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