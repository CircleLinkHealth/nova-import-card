<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use CircleLinkHealth\SharedModels\Entities\Call;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;

class CallBackSchedulingTest extends NekatostrasClinicTestCase
{
    public function test_an_admin_can_store_multiple_calls()
    {
        [$patient1, $patient2] = $this->randomPatients(2);
        $call1                 = $this->newCallbackParams($patient1->id, null);
        $call2                 = $this->newCallbackParams($patient2->id, null);

        $resp = $this->actingAs($clhAdmin = $this->administrator()->first())
            ->call('post', route('api.callcreate-multi'), [$call1, $call2]);

        $resp->assertStatus(201);

        $call1['scheduler'] = $clhAdmin->id;
        $call2['scheduler'] = $clhAdmin->id;

        $this->assertDatabaseHas('calls', $call1);
        $this->assertDatabaseHas('calls', $call2);
    }

    public function test_as_a_nurse_it_assigns_callback_for_enrolled_patient_to_logged_in_nurse_when_no_nurse_selected()
    {
        $patient   = $this->patient()->with('patientInfo')->first();
        $careCoach = $this->careCoach()->first();

        $repo = app(NurseFinderEloquentRepository::class);
        $repo->assign($patient->id, $careCoach->id);
        $params = $this->newCallbackParams($patient->id, null);

        $this->assertEquals(Patient::ENROLLED, $patient->patientInfo->ccm_status);
        $this->assertEquals($careCoach->id, $repo->assignedNurse($patient->id)->nurse_user_id);

        $resp = $this->actingAs($careCoach)
            ->call('get', route('call.create', [$patient->id]), $params);

        $resp->assertStatus(201);

        $params['outbound_cpm_id'] = $params['scheduler'] = $careCoach->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_as_a_nurse_it_assigns_callback_to_selected_nurse()
    {
        $patient   = $this->patient()->with('patientInfo')->first();
        $careCoach = $this->careCoach()->first();

        app(NurseFinderEloquentRepository::class)->assign($patient->id, $careCoach->id);
        $params = $this->newCallbackParams($patient->id, null);

        $this->assertEquals(Patient::ENROLLED, $patient->patientInfo->ccm_status);

        $resp = $this->actingAs($careCoach)
            ->call('get', route('call.create', [$patient->id]), $params);

        $resp->assertStatus(201);

        $params['outbound_cpm_id'] = $params['scheduler'] = $careCoach->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_as_a_nurse_it_switches_unreachable_patient_to_enrolled_and_assigns_callback_to_logged_in_nurse_when_no_nurse_selected()
    {
        $patient   = $this->patient()->with('patientInfo')->first();
        $careCoach = $this->careCoach()->first();

        $patient->patientInfo->ccm_status                          = Patient::UNREACHABLE;
        $patient->patientInfo->no_call_attempts_since_last_success = 4;
        $patient->patientInfo->save();

        app(NurseFinderEloquentRepository::class)->assign($patient->id, $careCoach->id);
        $params = $this->newCallbackParams($patient->id, null);

        $resp = $this->actingAs($careCoach)
            ->call('get', route('call.create', [$patient->id]), $params);

        $resp->assertStatus(201);

        $params['outbound_cpm_id'] = $params['scheduler'] = $careCoach->id;

        $this->assertDatabaseHas('calls', $params);
        $this->assertDatabaseHas('patient_info', [
            'id'                                  => $patient->patientInfo->id,
            'user_id'                             => $patient->id,
            'no_call_attempts_since_last_success' => PatientWriteRepository::MARK_UNREACHABLE_AFTER_FAILED_ATTEMPTS - PatientWriteRepository::MAX_CALLBACK_ATTEMPTS,
            'ccm_status'                          => Patient::ENROLLED,
        ]);
    }

    public function test_as_an_admin_it_assigns_callback_to_selected_nurse()
    {
        $patient   = $this->patient()->with('patientInfo')->first();
        $admin     = $this->administrator()->first();
        $careCoach = $this->careCoach()->first();

        $params = $this->newCallbackParams($patient->id, $careCoach->id);

        $this->assertEquals(Patient::ENROLLED, $patient->patientInfo->ccm_status);

        $resp = $this->actingAs($admin)
            ->call('get', route('call.create', [$patient->id]), $params);

        $resp->assertStatus(201);

        $params['scheduler'] = $admin->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_as_an_admin_it_leaves_callback_unassigned_when_no_nurse_selected()
    {
        $patient = $this->patient()->with('patientInfo')->first();
        $admin   = $this->administrator()->first();

        $params = $this->newCallbackParams($patient->id, null);

        $this->assertEquals(Patient::ENROLLED, $patient->patientInfo->ccm_status);

        $resp = $this->actingAs($admin)
            ->call('get', route('call.create', [$patient->id]), $params);

        $resp->assertStatus(201);

        $params['scheduler'] = $admin->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_it_does_not_allow_scheduling_call_if_nurse_is_not_patient_assigned_nurse()
    {
        $patient   = $this->patient()->firstOrFail();
        $careCoach = $this->careCoach()->first();

        $repo = app(NurseFinderEloquentRepository::class);
        $repo->deleteAssignment($patient->id);
        $this->assertNotEquals($careCoach->id, optional($repo->assignedNurse($patient->id))->nurse_user_id);

        $params = $this->newCallbackParams($patient->id, null);

        $resp = $this->actingAs($careCoach)
            ->call('get', route('call.create', [$patient->id]), $params);

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
