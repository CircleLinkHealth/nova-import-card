<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Jobs\ProcessSinglePatientEligibility;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;

class CsvEligibilityValidationTest extends TestCase
{
    use UserHelpers;

    private $practice;

    /**
     * @var \CircleLinkHealth\Eligibility\ProcessEligibilityService
     */
    private $service;

    protected function setUp(): void
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

        $jobs->each(function ($job) use ($batch) {
            (new ProcessSinglePatientEligibility($job->id, true))
                ->handle();
        });

        $jobs = $batch->eligibilityJobs()->get();
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
