<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit\CallSchedulingAlgo;

use App\Http\Controllers\CallController;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use Tests\CustomerTestCase;

class CallSchedulerTest extends CustomerTestCase
{
    public function test_callbacks_do_not_affect_unsuccessful_attemts_count()
    {
        $patientInfo = $this->patient()->patientInfo()->create(['no_call_attempts_since_last_success' => 4]);

        $patientSummary = $this->app->make(PatientWriteRepository::class)->updateCallLogs($patientInfo, true);

        $this->assertDatabaseMissing('patient_info', [
            'id'         => $patientInfo->id,
            'ccm_status' => Patient::UNREACHABLE,
        ]);
    }

    public function test_it_pauses_patient_after_5_unsuccessful_calls()
    {
        $patientInfo = $this->patient()->patientInfo()->create(['no_call_attempts_since_last_success' => 4]);

        $patientSummary = $this->app->make(PatientWriteRepository::class)->updateCallLogs($patientInfo, false);

        $this->assertDatabaseHas('patient_info', [
            'id'         => $patientInfo->id,
            'ccm_status' => Patient::UNREACHABLE,
        ]);
    }

    public function test_only_admins_can_change_nurse_patient_association()
    {
        $this->assertFalse(app(CallController::class)->canChangeNursePatientRelation($this->careCoach()));
        $this->assertTrue(app(CallController::class)->canChangeNursePatientRelation($this->superadmin()));
    }

    public function tests_it_schedules_call_for_next_weekday_for_patient_without_call_windows()
    {
        $patient = $this->patient()->patientInfo()->create([]);

        $now           = Carbon::now();
        $dateTimeArray = (new PatientContactWindow())->getEarliestWindowForPatientFromDate($patient, $now);

        do {
            $now->addDay();
        } while ( ! $now->isWeekday());

        $this->assertEquals([
            'day'          => $now->toDateTimeString(),
            'window_start' => Carbon::parse('09:00:00')->format('H:i'),
            'window_end'   => Carbon::parse('17:00:00')->format('H:i'),
        ], $dateTimeArray);
    }
}
