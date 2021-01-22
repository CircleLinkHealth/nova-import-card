<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTargetPatientsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->dropForeign('target_patients_batch_id_foreign');
            $table->dropForeign('target_patients_ehr_id_foreign');
            $table->dropForeign('target_patients_enrollee_id_foreign');
            $table->dropForeign('target_patients_user_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->foreign('batch_id')->references('id')->on('eligibility_batches')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('ehr_id')->references('id')->on('ehrs')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('enrollee_id')->references('id')->on('enrollees')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
