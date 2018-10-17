<?php

namespace Tests\Feature;

use App\WT1CsvParser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WT1ImportTest extends TestCase
{

    public function test_csv_to_json() {

        $parser = new WT1CsvParser();
        $fileName = getcwd() . '/Tests/Feature/wt1_import/csv_sample.csv';
        $parser->parseFile($fileName);
        $patients = $parser->toArray();
        $this->assertTrue(count($patients) === 1);
    }

    public function test_csv_2_to_json() {
        $parser = new WT1CsvParser();
        $fileName = getcwd() . '/Tests/Feature/wt1_import/clh_patient_list2.csv';
        $parser->parseFile($fileName);
        $patients = $parser->toArray();
        $this->assertTrue(count($patients) === 5);
    }

}
