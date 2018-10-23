<?php

namespace Tests\Feature;

use App\EligibilityJob;
use App\Enrollee;
use App\WT1CsvParser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WT1ImportTest extends TestCase
{

//    public function test_csv_to_json()
//    {
//
//        $parser   = new WT1CsvParser();
//        $fileName = getcwd() . '/Tests/Feature/wt1_import/csv_sample.csv';
//        $parser->parseFile($fileName);
//        $patients = $parser->toArray();
//        $this->assertTrue(count($patients) === 1);
//    }
//
//    public function test_csv_2_to_json()
//    {
//        $parser   = new WT1CsvParser();
//        $fileName = getcwd() . '/Tests/Feature/wt1_import/clh_patient_list2.csv';
//        $parser->parseFile($fileName);
//        $patients = $parser->toArray();
//        $this->assertTrue(count($patients) === 5);
//    }
//
    public function test_csv_3_to_json()
    {
        $parser   = new WT1CsvParser();
        $fileName = getcwd() . '/Tests/Feature/wt1_import/clh_ccm_2.csv';
        $parser->parseFile($fileName);
        $patients = $parser->toArray();
        $this->assertTrue(count($patients) === 5);
    }

//    public function test_batch_process()
//    {
//        $noOfJobsBefore = EligibilityJob::count();
//        $enrolsBefore = Enrollee::count();
//
//        //create batch and eligibility jobs
//        \Artisan::call('wt1:importCsv');
//
//        //process the batch
//        \Artisan::call('batch:process');
//
//        $noOfJobsAfter = EligibilityJob::count();
//        $this->assertTrue($noOfJobsAfter > $noOfJobsBefore);
//
//        $enrolsAfter = Enrollee::count();
//        $this->assertTrue($enrolsAfter > $enrolsBefore);
//
//        $latestJob = EligibilityJob::latest()->first();
//        $this->assertTrue($latestJob->outcome != null);
//    }

}
