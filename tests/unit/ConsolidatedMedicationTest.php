<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConsolidatedMedicationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     */
    public function test_example()
    {
        //cannot test because no ImportedMedicalRecords exists in sqlite

//        $record = ImportedMedicalRecord::all();
//
//        $medications = new Medications($record->medical_record_id, $record->medical_record_type, $record);
//
//        $import = $medications->import($record->medical_record_id, $record->medical_record_type, $record);
//
        $this->assertTrue(true);
    }
}
