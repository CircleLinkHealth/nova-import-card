<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Call;
use App\Services\Calls\SchedulerService;
use Tests\CustomerTestCase;

class CallBackSchedulingTest extends CustomerTestCase
{
    public function test_as_a_nurse_it_assigns_callback_to_logged_in_nurse_when_no_nurse_selected()
    {
        app(NurseFinderEloquentRepository::class)->assign($this->patient()->id, $this->careCoach()->id);
        $params = $this->newCallbackParams($this->patient()->id, null);

        $resp = $this->actingAs($this->careCoach())
            ->call('get', route('call.create', [$this->patient()->id]), $params);

        $resp->assertStatus(201);

        $params['outbound_cpm_id'] = $params['scheduler'] = $this->careCoach()->id;

        $this->assertDatabaseHas('calls', $params);
    }

    public function test_as_a_nurse_it_assigns_callback_to_selected_nurse()
    {
    }

    public function test_as_an_admin_it_assigns_callback_to_selected_nurse()
    {
    }

    public function test_as_an_admin_it_leaves_callback_unassigned_when_no_nurse_selected()
    {
    }
    
    public function test_an_admin_can_Store_multiple_calls() {
    
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
