<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePatientInfoForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $keysToDrop = [
            'patient_info_care_plan_id_foreign',
            'patient_info_family_id_foreign',
            'patient_info_imported_medical_record_id_foreign',
            'patient_info_next_call_id_foreign',
            'patient_info_user_id_foreign',
        ];

        foreach ($keysToDrop as $key) {
            try {
                Schema::table('patient_info', function (Blueprint $table) use ($key) {
                    $table->dropForeign($key);
                });
            } catch (QueryException $e) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1091) {
                    Log::debug("Key `$key` does not exist. Nothing to delete." . __FILE__);
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
        Schema::table('patient_info', function (Blueprint $table) {
            //
        });
    }
}
