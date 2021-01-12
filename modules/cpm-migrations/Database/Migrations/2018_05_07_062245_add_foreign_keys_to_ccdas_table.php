<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdasTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->dropForeign('ccdas_batch_id_foreign');
            $table->dropForeign('ccdas_billing_provider_id_foreign');
            $table->dropForeign('ccdas_location_id_foreign');
            $table->dropForeign('ccdas_practice_id_foreign');
            $table->dropForeign('users_patient_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->foreign('batch_id')->references('id')->on('eligibility_batches')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('billing_provider_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('patient_id', 'users_patient_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }
}
