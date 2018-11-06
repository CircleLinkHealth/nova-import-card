<?php

namespace Tests\Unit;

use App\EligibilityBatch;
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
        $csv = getcwd() . '/Tests/Feature/EligibleCsvFormat/Numbered_Fields_1.csv';

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
        $this->assertEquals(EligibilityBatch::STATUSES['processing'], $batch->getStatus());

        $this->assertTrue($batch->hasJobs());

        $jobs = $batch->eligibilityJobs();
        $this->assertEquals(6, $jobs->count());

        //assert jobs have no errors
//        $this->assertAttributeEmpty();


    }

    public function testSingleFieldsPasses()
    {
        $csv = getcwd() . '/Tests/Feature/EligibleCsvFormat/Single_Fields_1.csv';

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

        $this->assertEquals(EligibilityBatch::STATUSES['processing'], $batch->getStatus());

        $this->assertTrue($batch->hasJobs());

        $jobs = $batch->eligibilityJobs();
        $this->assertEquals(5, $jobs->count());

    }

    protected function setUp()
    {
        parent::setUp();
        $this->service  = new ProcessEligibilityService();
        $this->practice = factory(Practice::class)->create();

    }
}
