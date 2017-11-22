<?php

namespace Tests\Unit;

use App\PatientContactWindow;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;

class CallSchedulerTest extends TestCase
{
    public function tests_it_schedules_call_for_next_weekday_for_patient_without_call_windows()
    {
        $patient = factory(User::class)->create()->patientInfo()->create([]);

        $now = Carbon::now();
        $dateTimeArray = (new PatientContactWindow())->getEarliestWindowForPatientFromDate($patient, $now);

        $this->assertEquals([
            'day'          => $now->addDay()->toDateTimeString(),
            'window_start' => Carbon::parse('09:00:00')->format('H:i'),
            'window_end'   => Carbon::parse('17:00:00')->format('H:i'),
        ], $dateTimeArray);
    }
}
