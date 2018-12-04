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

    public function test_csv_to_json()
    {
        $parser   = new WT1CsvParser();
        $fileName = getcwd() . '/Tests/Feature/wt1_import/clh_ccm_2.csv';
        $parser->parseFile($fileName);
        $patients = $parser->toArray();
        $this->assertTrue(count($patients) === 38);
    }

//    public function test_batch_process()
//    {
//        $noOfJobsBefore = EligibilityJob::count();
//        $enrolsBefore = Enrollee::count();
//
//        //create batch and eligibility jobs
//        \Artisan::call('wt1:importCsv');
//
//        //process the batch - need to call 4 times, because we have 1118 jobs, 300 processed each time
//        \Artisan::call('batch:process');
//        \Artisan::call('batch:process');
//        \Artisan::call('batch:process');
//        \Artisan::call('batch:process');
//
//        $noOfJobsAfter = EligibilityJob::count();
//        $this->assertTrue($noOfJobsAfter > $noOfJobsBefore);
//
//        $enrolsAfter = Enrollee::count();
//        $this->assertTrue($enrolsAfter > $enrolsBefore);
//
//        $incomplete = EligibilityJob::where('status', '!=', EligibilityJob::STATUSES['complete'])
//                      ->count();
//
//        $this->assertTrue($incomplete === 0);
//    }

}
