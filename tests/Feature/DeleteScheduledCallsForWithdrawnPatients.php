<?php

namespace Tests\Feature;

use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Customer\Entities\Patient;
use Tests\CustomerTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteScheduledCallsForWithdrawnPatients extends CustomerTestCase
{
    public function test_it_deletes_scheduled_calls_when_patient_status_changes_to_withdrawn()
    {
        $this->conductTest(Patient::WITHDRAWN);
    }
    
    public function test_it_deletes_scheduled_calls_when_patient_status_changes_to_withdrawn_on_1st_call()
    {
        $this->conductTest(Patient::WITHDRAWN_1ST_CALL);
    }
    
    public function test_it_deletes_scheduled_calls_when_patient_status_changes_to_paused()
    {
        $this->conductTest(Patient::PAUSED);
    }
    public function test_it_deletes_scheduled_calls_when_patient_status_changes_to_unreachable()
    {
        $this->conductTest(Patient::UNREACHABLE);
    }
    
    private function conductTest(string $newCcmStatus)
    {
        $service = app(SchedulerService::class);
        $patient = $this->patient();
        $date        = now()->addWeek()->toDateString();
        $windowStart = '09:00';
        $windowEnd   = '17:00';
        
        $service->storeScheduledCall($patient->id, $windowStart, $windowEnd, $date, $this->careCoach()->id);
        $this->assertDatabaseHas(
            'calls',
            [
                'inbound_cpm_id' => $patient->id,
                'scheduler'      => $this->careCoach()->id,
                'status'         => 'scheduled',
                'scheduled_date' => $date,
                'window_start'   => $windowStart,
                'window_end'     => $windowEnd,
            ]
        );
    
        $patient->patientInfo->setCcmStatusAttribute($newCcmStatus);
        $this->assertDatabaseMissing(
            'calls',
            [
                'inbound_cpm_id' => $patient->id,
                'scheduler'      => $this->careCoach()->id,
                'status'         => 'scheduled',
                'scheduled_date' => $date,
                'window_start'   => $windowStart,
                'window_end'     => $windowEnd,
            ]
        );
    }
}
