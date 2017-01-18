<?php

use App\Models\CCD\CcdInsurancePolicy;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateCcdaIdAndType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_insurance_policies', function (Blueprint $table) {
            $table->string('medical_record_type')->nullable()->after('id');
            $table->unsignedInteger('medical_record_id')->nullable()->after('id');
        });

        $tables = [
            CcdInsurancePolicy::class,
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
