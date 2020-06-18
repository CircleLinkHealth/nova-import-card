<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\CareAmbassadorLog;
use App\Traits\Tests\TimeHelpers;
use Tests\CustomerTestCase;

class CareAmbassadorTimeTest extends CustomerTestCase
{
    use TimeHelpers;
    const ACTIVITY_TITLE_LOADING_PATIENT = 'CA - Loading next patient';

    const ACTIVITY_TITLE_TO_SKIP_FROM_CA_TIME = 'CA - No more patients';

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_time_is_added_in_total_time_in_system()
    {
        $ca       = $this->careAmbassador();
        $enrollee = $this->enrollee();
        $this->addTime($ca, $enrollee->user, 1, false, false, false, null, 0, self::ACTIVITY_TITLE_LOADING_PATIENT);
        $this->addTime($ca, $enrollee->user, 10, false, false, false, null, $enrollee->id, "CA - $enrollee->id");
        $this->addTime($ca, $enrollee->user, 1, false, false, false, null, 0, self::ACTIVITY_TITLE_TO_SKIP_FROM_CA_TIME);

        $report = CareAmbassadorLog::createOrGetLogs($ca->careAmbassador->id);
        self::assertEquals((1 * 60) + (10 * 60), $report->total_time_in_system);
        self::assertEquals(10 * 60, $enrollee->fresh()->total_time_spent);
    }
}
