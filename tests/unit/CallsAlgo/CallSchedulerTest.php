<?php

namespace Tests\Unit\CallsAlgo;

use App\Patient;
use App\PatientContactWindow;
use App\Repositories\PatientWriteRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CallSchedulerTest extends TestCase
{
    use DatabaseTransactions;

    public function tests_it_schedules_call_for_next_weekday_for_patient_without_call_windows()
    {
        $patient = factory(User::class)->create()->patientInfo()->create([]);

        $now           = Carbon::now();
        $dateTimeArray = (new PatientContactWindow())->getEarliestWindowForPatientFromDate($patient, $now);

        do {
            $now->addDay();
        } while (! $now->isWeekday());

        $this->assertEquals([
            'day'          => $now->toDateTimeString(),
            'window_start' => Carbon::parse('09:00:00')->format('H:i'),
            'window_end'   => Carbon::parse('17:00:00')->format('H:i'),
        ], $dateTimeArray);
    }

    public function test_it_pauses_patient_after_5_unsuccessful_calls()
    {
        $patientInfo = factory(User::class)->create()->patientInfo()->create(['no_call_attempts_since_last_success' => 4]);

        $patientSummary = $this->app->make(PatientWriteRepository::class)->updateCallLogs($patientInfo, false);

        $this->assertDatabaseHas('patient_info', [
            'id'         => $patientInfo->id,
            'ccm_status' => Patient::UNREACHABLE,
        ]);

        $patientInfo->fresh();

        $this->assertEquals(Patient::UNREACHABLE, $patientInfo->ccm_status);
    }
}
