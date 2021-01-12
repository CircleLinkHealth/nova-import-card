<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Nurse;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Services\NursesPerformanceReportService;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;

class PerformanceTest extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Artisan::call('db:seed', ['--class' => \PopulateNursePerformanceSeeder::class]);
    }

    public function test_example()
    {
        $this->getFakeReportData();
    }

    private function getFakeReportData()
    {
        $date = Carbon::createFromDate(2020, 3, 1)->startOfMonth();

        return app(NursesPerformanceReportService::class)->collectData($date);
    }
}
