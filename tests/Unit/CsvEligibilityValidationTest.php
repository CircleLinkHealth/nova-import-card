<?php

namespace Tests\Unit;

use App\EligibilityBatch;
use App\Jobs\ProcessSinglePatientEligibility;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Csv\CsvPatientList;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CsvEligibilityValidationTest extends TestCase
{
    use UserHelpers;

    private $service;

    private $practice;

    /**
     *
     *
     * @return void
     */
    public function testNumberedFieldsPasses()
    {
        $csv = base_path('tests/Feature/EligibleCsvFormat/Numbered_Fields_1.csv');

        $this->assertFileExists($csv);

        $patients = parseCsvToArray($csv);

        $csvPatientList = new CsvPatientList(collect($patients));
        $isValid        = $csvPatientList->guessValidator();

        $this->assertTrue($isValid);

        $batch = $this->service->createSingleCSVBatch($patients, $this->practice->id, false, false, true);


        $this->assertDatabaseHas('eligibility_batches', [
            'id' => $batch->id,
        ]);

        $result = $this->service->processCsvForEligibility($batch);

        if ($result) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();
        }
        $this->assertEquals('processing', $batch->getStatus());

        $this->assertTrue($batch->hasJobs());

        $jobs = $batch->eligibilityJobs()->get();
        $this->assertEquals(6, $jobs->count());

        $jobs->map(function ($job) use ($batch) {
            (new ProcessSinglePatientEligibility(
                collect([$job->data]),
                $job,
                $batch,
                $batch->practice
            ))->handle();
        });

        foreach($jobs as $job){
            $this->assertEquals("3", $job->status);
            $this->assertNotNull($job->outcome);
        }

    }

    public function testSingleFieldsPasses()
    {
        $csv = base_path('tests/Feature/EligibleCsvFormat/Single_Fields_1.csv');

        $this->assertFileExists($csv);

        $patients = parseCsvToArray($csv);

        $csvPatientList = new CsvPatientList(collect($patients));
        $isValid        = $csvPatientList->guessValidator();

        $this->assertTrue($isValid);
        $batch = $this->service->createSingleCSVBatch($patients, $this->practice->id, false, false, true);

        $this->assertDatabaseHas('eligibility_batches', [
            'id' => $batch->id,
        ]);

        $result = $this->service->processCsvForEligibility($batch);

        if ($result) {
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
                collect([$job->data]),
                $job,
                $batch,
                $batch->practice
            ))->handle();
        });

        foreach($jobs as $job){
            $this->assertEquals("3", $job->status);
            $this->assertNotNull($job->outcome);
        }

    }

    protected function setUp()
    {
        parent::setUp();
        $this->service  = new ProcessEligibilityService();
        $this->practice = factory(Practice::class)->create();

    }
}
