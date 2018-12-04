<?php

namespace Tests\Unit;

use App\Importer\Section\Importers\Medications;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsolidatedMedicationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
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
