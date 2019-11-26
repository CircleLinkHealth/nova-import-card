<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePatientInfoForeignKeys extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! isOnSqlite()) {
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
                    if (1091 == $errorCode) {
                        Log::debug("Key `${key}` does not exist. Nothing to delete.".__FILE__);
                    }
                }
            }
        }
    }
}
