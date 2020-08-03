<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Services\Enrollment\UpdateEnrollable;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

trait CareAmbassadorHelpers
{
    protected function performActionOnEnrollee(Enrollee $enrollee, $actionType = Enrollee::UNREACHABLE, $input = [])
    {
        $data = [];

        if (Enrollee::UNREACHABLE === $actionType) {
            $data = $this->unreachableInputForRequest($input);
        }

        if (Enrollee::CONSENTED === $actionType) {
            $data = $this->consentedInputForRequest($enrollee, $input);
        }

        if (Enrollee::REJECTED === $actionType || Enrollee::SOFT_REJECTED === $actionType) {
            $data = $this->rejectedInputForRequest($actionType, $input);
        }

        UpdateEnrollable::update($enrollee->id, collect($data));
    }

    private function consentedInputForRequest(Enrollee $enrollee, $data = []): array
    {
        $data['home_phone'] ??= $enrollee->home_phone;
        $data['cell_phone'] ??= $enrollee->cell_phone;
        $data['other_phone'] ??= $enrollee->other_phone;
        $data['preferred_phone'] ??= 'home';

        $data['agent_phone'] ??= null;
        $data['agent_email'] ??= null;
        $data['agent_relationship'] ??= null;
        $data['agent_name'] ??= null;

        $data['address'] ??= $enrollee->address;
        $data['address_2'] ??= $enrollee->address_2;
        $data['zip'] ??= $enrollee->zip;
        $data['state'] ??= $enrollee->state;
        $data['city'] ??= $enrollee->email;
        $data['email'] ??= $enrollee->email;
        $data['extra'] ??= null;
        $data['days'] ??= ['1', '2', '3', '4', '5'];
        $data['times'] ??= ['09:00-12:00'];

        $data['status'] ??= Enrollee::CONSENTED;

        return $data;
    }

    private function createEligibilityJobDataForEnrollee(Enrollee $enrollee)
    {
        $job = factory(\CircleLinkHealth\Eligibility\Entities\EligibilityJob::class)->create();

        $job->hash = $enrollee->practice->name.$enrollee->first_name.$enrollee->last_name.$enrollee->mrn.$enrollee->city.$enrollee->state.$enrollee->zip;

        $job->data = [
            'patient_id'              => $enrollee->mrn,
            'mrn'                     => $enrollee->mrn,
            'last_name'               => $enrollee->last_name,
            'first_name'              => $enrollee->first_name,
            'date_of_birth'           => $enrollee->dob->toDateString(),
            'dob'                     => $enrollee->dob->toDateString(),
            'gender'                  => collect(['M', 'F'])->random(),
            'lang'                    => $enrollee->lang,
            'preferred_provider'      => $enrollee->providerFullName,
            'referring_provider_name' => $enrollee->providerFullName,
            'cell_phone'              => $enrollee->cell_phone,
            'home_phone'              => $enrollee->home_phone,
            'other_phone'             => $enrollee->other_phone,
            'primary_phone'           => null,
            'email'                   => $enrollee->email,
            'street'                  => $enrollee->address,
            'address_line_1'          => $enrollee->address,
            'street2'                 => $enrollee->address_2,
            'address_line_2'          => $enrollee->address_2,
            'city'                    => $enrollee->city,
            'state'                   => $enrollee->state,
            'zip'                     => $enrollee->zip,
            'postal_code'             => $enrollee->zip,
            'primary_insurance'       => $enrollee->primary_insurance,
            'secondary_insurance'     => $enrollee->secondary_insturance,
            'problems'                => [
                [
                    'name'       => 'Hypertension',
                    'start_date' => \Carbon\Carbon::now()->toDateString(),
                    'code'       => 'I10',
                    'code_type'  => 'ICD-10',
                ],
                [
                    'name'       => 'Asthma',
                    'start_date' => \Carbon\Carbon::now()->toDateString(),
                    'code'       => 'J45.901',
                    'code_type'  => 'ICD-10',
                ],
            ],
            'allergies'   => [['name' => 'peanut']],
            'medications' => [],
            'is_demo'     => 'true',
        ];
        $job->save();

        $enrollee->eligibility_job_id = $job->id;
        $enrollee->save();
    }

    private function rejectedInputForRequest($actionType = Enrollee::REJECTED, $data = []): array
    {
        $data['reason'] ??= 'no longer interested';
        $data['reason_other'] ??= 'Other Reason';
        $data['status'] ??= $actionType;

        return $data;
    }

    private function unreachableInputForRequest($data = []): array
    {
        $data['reason'] ??= 'no longer interested';
        $data['reason_other'] ??= 'Other Reason';
        $data['utc_callback'] ??= null;
        $data['status'] ??= Enrollee::UNREACHABLE;

        return $data;
    }
}
