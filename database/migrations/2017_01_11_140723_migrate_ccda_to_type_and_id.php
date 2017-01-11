<?php

use App\Importer\Models\ImportedItems\AllergyImport;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Importer\Models\ImportedItems\ProblemImport;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Database\Migrations\Migration;

class MigrateCcdaToTypeAndId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            AllergyLog::class,
            DemographicsLog::class,
            DocumentLog::class,
            MedicationLog::class,
            ProblemLog::class,
            ProviderLog::class,

            AllergyImport::class,
            DemographicsImport::class,
            MedicationImport::class,
            ProblemImport::class,
        ];

        foreach ($tables as $t) {
            $allRows = app($t)->all();

            foreach ($allRows as $row) {
                if ($row->ccda_id) {
                    $row->medical_record_type = Ccda::class;
                    $row->medical_record_id = $row->ccda_id;
                    $row->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
