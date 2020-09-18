<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit\CallSchedulingAlgo;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use CircleLinkHealth\SharedModels\Entities\Call;
use App\Http\Controllers\CallController;
use App\Services\Calls\SchedulerService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use Tests\CustomerTestCase;

class CallSchedulerTest extends CustomerTestCase
{
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

    public function test_unsuccessful_calls_in_tasks_dont_reset_count()
    {
        $call = Call::create(
            [
                'type'            => 'task',
                'sub_type'        => SchedulerService::CALL_BACK_TYPE,
                'service'         => 'phone',
                'status'          => Call::SCHEDULED,
                'attempt_note'    => 'This is a task',
                'scheduler'       => $this->provider()->id,
                'inbound_cpm_id'  => $this->patient()->id,
                'outbound_cpm_id' => $this->app->make(NurseFinderEloquentRepository::class)->find($this->patient()->id),
                'asap'            => true,
            ]
        );

        $response = $this->actingAs($this->careCoach())
            ->call(
                'POST',
                route('patient.note.store', [$this->patient()->id]),
                [
                    'ccm_status'      => Patient::ENROLLED,
                    'general_comment' => 'All Good',
                    'type'            => 'General (Clinical)',
                    'performed_at'    => '2019-11-23T16:47',
                    'phone'           => 'outbound',
                    'tcm'             => 0,
                    'summary'         => 'Test Summary',
                    'body'            => 'Test Body hello there',
                    'patient_id'      => $this->patient()->id,
                    'logger_id'       => $this->careCoach()->id,
                    'author_id'       => $this->careCoach()->id,
                    'programId'       => $this->practice()->id,
                    'call_status'     => Call::NOT_REACHED,
                    'task_status'     => Call::DONE,
                    'task_id'         => $call->id,
                ]
            );

        $response->assertStatus(302);
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
