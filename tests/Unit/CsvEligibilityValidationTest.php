<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\EligibilityBatch;
use App\Jobs\ProcessSinglePatientEligibility;
use App\Services\CCD\ProcessEligibilityService;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CsvEligibilityValidationTest extends TestCase
{
    use UserHelpers;

    private $practice;

    /**
     * @var ProcessEligibilityService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service  = new ProcessEligibilityService();
        $this->practice = factory(Practice::class)->create();
    }

    public function test_numbered_fields_passes()
    {
        $csv = base_path('tests/Feature/EligibleCsvFormat/Numbered_Fields_1.csv');

        $this->assertFileExists($csv);

        $batch = $this->service->createSingleCSVBatch($this->practice->id, false, false, true);

        $this->assertDatabaseHas('eligibility_batches', [
            'id' => $batch->id,
        ]);

        $results = $this->service->createEligibilityJobFromCsvBatch($batch, $csv);

        if ($results) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();
        }
        $this->assertEquals('processing', $batch->getStatus());

        $this->assertTrue($batch->hasJobs());

        $jobs = $batch->eligibilityJobs()->get();
        $this->assertEquals(6, $jobs->count());

        $jobs->map(function ($job) use ($batch) {
            (new ProcessSinglePatientEligibility(
                $job,
                $batch,
                $batch->practice
            ))->handle();
        });

        foreach ($jobs as $job) {
            $this->assertEquals('3', $job->status);
            $this->assertNotNull($job->outcome);
        }
    }

    public function test_single_fields_passes()
    {
        $csv = base_path('tests/Feature/EligibleCsvFormat/Single_Fields_1.csv');

        $this->assertFileExists($csv);

        $batch = $this->service->createSingleCSVBatch($this->practice->id, false, false, true);

        $this->assertDatabaseHas('eligibility_batches', [
            'id' => $batch->id,
        ]);

        $results = $this->service->createEligibilityJobFromCsvBatch($batch, $csv);

        if ($results) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();
        }
        $this->assertDatabaseHas('eligibility_batches', [
            'id' => $batch->id,
        ]);

        $this->assertEquals('processing', $batch->getStatus());

        $this->assertTrue($batch->hasJobs());

        $jobs = $batch->eligibilityJobs()->get();
        $this->assertEquals(5, $jobs->count());

        $jobs->map(function ($job) use ($batch) {
            (new ProcessSinglePatientEligibility(
                $job,
                $batch,
                $batch->practice
            ))->handle();
        });

        foreach ($jobs as $job) {
            $this->assertEquals('3', $job->status);
            $this->assertNotNull($job->outcome);
        }
    }
}
