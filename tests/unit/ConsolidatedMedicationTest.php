<?php

namespace Tests\Unit;

use App\Importer\Section\Importers\Medications;
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

        $record = DB::table('imported_medical_records')->get()->random();

        $medications = new Medications($record->medical_record_id, $record->medical_record_type, $record);

        $import = $medications->import($record->medical_record_id, $record->medical_record_type, $record);

        $x =1 ;

    }
}
