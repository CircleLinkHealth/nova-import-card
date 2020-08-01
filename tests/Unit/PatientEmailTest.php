<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Core\Entities\AppConfig;
use Tests\CustomerTestCase;

class PatientEmailTest extends CustomerTestCase
{
    protected $nurse;

    protected $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = $this->patient();
        $this->nurse   = $this->careCoach();

        $this->enableFeatureForNurse();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_asynchronous_back_end_validation()
    {
        $responseData = $this->actingAs($this->nurse)->call('POST', route('patient-email.validate', [
            $this->patient->id,
        ]), ['patient_email_subject' => $this->patient->first_name,
            'patient_email_body'     => $this->patient->first_name,
            'custom_patient_email'   => 'test@careplanmanager.com', ])
            ->getOriginalContent();

        $this->assertTrue(in_array('Email subject contains patient PHI: First Name', $responseData['messages']));
        $this->assertTrue(in_array('Email body contains patient PHI: First Name', $responseData['messages']));
        $this->assertTrue(in_array('Email is invalid.', $responseData['messages']));
    }

    private function enableFeatureForNurse()
    {
        AppConfig::set('enable_patient_email_for_user', $this->nurse->id);
    }
}
