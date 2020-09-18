<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\CpmAdmin\Console\Commands\CountPatientMonthlySummaryCalls;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Tests\CustomerTestCase;

class CallCountTest extends CustomerTestCase
{
    public function test_it_counts_calls_from_last_month_if_last_month_call_status_has_changed()
    {
        $callReached    = $this->createCall(Call::REACHED);
        $callNotReached = $this->createCall(Call::NOT_REACHED);

        $pms = PatientMonthlySummary::updateOrCreate([
            'patient_id' => $this->patient()->id,
            'month_year' => now()->subMonth()->startOfMonth(),
        ]);

        $service = $this->app->make(CountPatientMonthlySummaryCalls::class);
        $service->countCalls($pms->month_year, [$pms->patient_id]);

        $this->assertDatabaseHas((new PatientMonthlySummary())->getTable(), [
            'no_of_calls'            => 2,
            'no_of_successful_calls' => 1,
            'patient_id'             => $pms->patient_id,
            'month_year'             => $pms->month_year,
            'id'                     => $pms->id,
        ]);

        $callNotReached->status = Call::REACHED;
        $callNotReached->save();

        $this->assertDatabaseHas((new PatientMonthlySummary())->getTable(), [
            'no_of_calls'            => 2,
            'no_of_successful_calls' => 2,
            'patient_id'             => $pms->patient_id,
            'month_year'             => $pms->month_year,
            'id'                     => $pms->id,
        ]);
    }

    private function createCall(string $status)
    {
        return Call::create([
            'type'    => SchedulerService::CALL_TYPE,
            'service' => 'phone',
            'status'  => $status,

            'attempt_note' => '',

            'scheduler' => $this->careCoach()->id,
            'is_manual' => 1,

            'inbound_phone_number' => '',

            'outbound_phone_number' => '',

            'inbound_cpm_id'  => $this->patient()->id,
            'outbound_cpm_id' => $this->careCoach()->id,

            'scheduled_date' => now()->subMonth()->format('Y-m-d'),
            'called_date'    => now()->subMonth()->format('Y-m-d'),
            'window_start'   => '09:00',
            'window_end'     => '17:00',

            'is_cpm_outbound' => true,
        ]);
    }
}
