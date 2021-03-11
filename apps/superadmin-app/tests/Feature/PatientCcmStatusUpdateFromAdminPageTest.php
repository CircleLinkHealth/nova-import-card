<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PatientCcmStatusUpdateFromAdminPageTest extends CustomerTestCase
{
    use UserHelpers;
    use WithoutMiddleware;
    protected $admin;
    protected $nurse;
    protected $patient;

    protected $practice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->practice = $this->practice();
        $this->patient  = $this->patient();
        $this->admin    = $this->superadmin();

        $this->nurse = $this->careCoach();
    }

    public function test_withdrawn_1st_call_status_is_saved_on_mass_withdrawal()
    {
        auth()->login($this->admin);

        $response = $this->actingAs($this->admin)->call(
            'GET',
            route('admin.users.doAction'),
            [
                'action'           => 'withdraw',
                'withdrawn-reason' => 'No Longer Interested',
                'users'            => [
                    $this->patient->id,
                ],
            ]
        );

        $info = $this->patient->patientInfo()->first();
        $this->assertEquals($info->ccm_status, 'withdrawn_1st_call');
        $this->assertEquals($info->withdrawn_reason, 'No Longer Interested');
    }

    public function test_withdrawn_from_all_users_page()
    {
        $this->actingAs($this->nurse);

        $this->assertTrue(Patient::ENROLLED == $this->patient->getCcmStatus());
        $this->assertTrue($this->patient->onFirstCall());

        $this->actingAs($this->admin)->call('GET', route('admin.users.doAction'), $this->getAllUsersActionInput());

        $this->patient->load('patientInfo');

        $this->assertTrue(Patient::WITHDRAWN_1ST_CALL == $this->patient->getCcmStatus());
    }

    private function getAllUsersActionInput()
    {
        return [
            'action'           => 'withdraw',
            'withdrawn_reason' => 'test',
            'users'            => [$this->patient->id],
            'withdrawn-reason' => 'test',
        ];
    }
}
