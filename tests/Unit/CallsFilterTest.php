<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Call;
use App\Services\CallService;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\TestCase;

class CallsFilterTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;

    /**
     * Nektarios Bug as described in https://circlelinkhealth.atlassian.net/browse/CPM-1787.
     *
     * Steps to reproduce:
     *  - For the same patient, create 2 ASAP tasks for the same day, and assign each task to a different nurse.
     *
     * @return void
     */
    public function test_it_filters_2_nurses_having_an_asap_task_for_the_same_patient_on_the_same_day()
    {
        //setup conditions
        $practice = factory(Practice::class)->create();
        $callDate = \Carbon\Carbon::now();
        $admin    = $this->createUser($practice->id, 'administrator');
        auth()->login($admin); //test needs a logged in user
        $patient  = $this->createUser($practice->id, 'participant');
        $nurse1   = $this->createUser($practice->id, 'care-center');
        $nurse2   = $this->createUser($practice->id, 'care-center');
        $callData = [
            'inbound_cpm_id' => $patient->id,
            'scheduled_date' => $callDate->toDateString(),
            'asap'           => 1,
            'status'         => 'scheduled',
        ];
        $call1   = factory(Call::class)->create(array_merge($callData, ['outbound_cpm_id' => $nurse1->id]));
        $call2   = factory(Call::class)->create(array_merge($callData, ['outbound_cpm_id' => $nurse2->id]));
        $service = app(CallService::class);

        //perform test
        $nurse1Calls = $service->filterCalls('scheduled', 'asap', $callDate->toDateString(), $nurse1->id);
        $nurse2Calls = $service->filterCalls('scheduled', 'asap', $callDate->toDateString(), $nurse2->id);

        //evaluate
        $this->assertEquals(1, $nurse1Calls->count());
        $this->assertEquals($call1->id, optional($nurse1Calls->first())->id);

        $this->assertEquals(1, $nurse2Calls->count());
        $this->assertEquals($call2->id, optional($nurse2Calls->first())->id);
    }
}
