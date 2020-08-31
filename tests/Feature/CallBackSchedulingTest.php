<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Call;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use Tests\CustomerTestCase;

class CallBackSchedulingTest extends CustomerTestCase
{
    public function test_an_admin_can_store_multiple_calls()
    {
    }

    public function test_as_a_nurse_it_assigns_callback_for_enrolled_patient_to_logged_in_nurse_when_no_nurse_selected()
    {
        app(NurseFinderEloquentRepository::class)->assign($this->patient()->id, $this->careCoach()->id);
        $params = $this->newCallbackParams($this->patient()->id, null);

        $this->assertEquals(Patient::ENROLLED, $this->patient()->patientInfo->ccm_status);

        $resp = $this->actingAs($this->careCoach())
            ->call('get', route('call.create', [$this->patient()->id]), $params);

        $resp->assertStatus(201);

        $params['outbound_cpm_id'] = $params['scheduler'] = $this->careCoach()->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_as_a_nurse_it_assigns_callback_to_selected_nurse()
    {
        app(NurseFinderEloquentRepository::class)->assign($this->patient()->id, $this->careCoach()->id);
        $params = $this->newCallbackParams($this->patient()->id, null);

        $this->assertEquals(Patient::ENROLLED, $this->patient()->patientInfo->ccm_status);

        $resp = $this->actingAs($this->careCoach())
            ->call('get', route('call.create', [$this->patient()->id]), $params);

        $resp->assertStatus(201);

        $params['outbound_cpm_id'] = $params['scheduler'] = $this->careCoach()->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_as_a_nurse_it_switches_unreachable_patient_to_enrolled_and_assigns_callback_to_logged_in_nurse_when_no_nurse_selected()
    {
        $this->patient()->patientInfo->ccm_status                          = Patient::UNREACHABLE;
        $this->patient()->patientInfo->no_call_attempts_since_last_success = 4;
        $this->patient()->patientInfo->save();

        app(NurseFinderEloquentRepository::class)->assign($this->patient()->id, $this->careCoach()->id);
        $params = $this->newCallbackParams($this->patient()->id, null);

        $resp = $this->actingAs($this->careCoach())
            ->call('get', route('call.create', [$this->patient()->id]), $params);

        $resp->assertStatus(201);

        $params['outbound_cpm_id'] = $params['scheduler'] = $this->careCoach()->id;

        $this->assertDatabaseHas('calls', $params);
        $this->assertDatabaseHas('patient_info', [
            'id'                                  => $this->patient()->patientInfo->id,
            'user_id'                             => $this->patient()->id,
            'no_call_attempts_since_last_success' => PatientWriteRepository::MARK_UNREACHABLE_AFTER_FAILED_ATTEMPTS - PatientWriteRepository::MAX_CALLBACK_ATTEMPTS,
            'ccm_status'                          => Patient::ENROLLED,
        ]);
    }

    public function test_as_an_admin_it_assigns_callback_to_selected_nurse()
    {
        $params = $this->newCallbackParams($this->patient()->id, $this->careCoach()->id);

        $this->assertEquals(Patient::ENROLLED, $this->patient()->patientInfo->ccm_status);

        $resp = $this->actingAs($this->superadmin())
            ->call('get', route('call.create', [$this->patient()->id]), $params);

        $resp->assertStatus(201);

        $params['scheduler'] = $this->superadmin()->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_as_an_admin_it_leaves_callback_unassigned_when_no_nurse_selected()
    {
        $params = $this->newCallbackParams($this->patient()->id, null);

        $this->assertEquals(Patient::ENROLLED, $this->patient()->patientInfo->ccm_status);

        $resp = $this->actingAs($this->superadmin())
            ->call('get', route('call.create', [$this->patient()->id]), $params);

        $resp->assertStatus(201);

        $params['scheduler'] = $this->superadmin()->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_it_does_not_allow_scheduling_call_if_nurse_is_not_patient_assigned_nurse()
    {
        $params = $this->newCallbackParams($this->patient()->id, null);

        $resp = $this->actingAs($this->careCoach())
            ->call('get', route('call.create', [$this->patient()->id]), $params);

        $resp->assertStatus(403);
    }

    private function newCallbackParams(int $patientId, ?int $outboundCpmId)
    {
        return [
            'type'            => 'task',
            'sub_type'        => SchedulerService::CALL_BACK_TYPE,
            'service'         => 'phone',
            'status'          => Call::SCHEDULED,
            'attempt_note'    => 'this is a callback',
            'scheduler'       => $outboundCpmId,
            'inbound_cpm_id'  => $patientId,
            'outbound_cpm_id' => $outboundCpmId,
            'asap'            => true,
            'scheduled_date'  => now()->addDays(3)->toDateString(),
            'window_start'    => '11:00',
            'window_end'      => '16:00',
            'is_manual'       => true,
        ];
    }
}
