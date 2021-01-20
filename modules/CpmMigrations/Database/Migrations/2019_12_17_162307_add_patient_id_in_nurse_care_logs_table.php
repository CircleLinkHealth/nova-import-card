<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPatientIdInNurseCareLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_care_rate_logs', function (Blueprint $table) {
            $table->dropForeign(['patient_user_id']);
            $table->dropColumn(['patient_user_id', 'time_before', 'is_successful_call']);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_care_rate_logs', function (Blueprint $table) {
            $table->unsignedInteger('patient_user_id')->nullable(true)->default(null)->after('nurse_id');
            $table->unsignedInteger('time_before')->default(null)->after('ccm_type');
            $table->boolean('is_successful_call')->default(null)->after('increment');

            $table->foreign('patient_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
}
