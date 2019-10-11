<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNurseUserIdFromPatientInfo extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->unsignedInteger('nurse_user_id')->nullable(true)->default(null);
            $table->foreign('nurse_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            if (Schema::hasColumn('patient_info', 'nurse_user_id')) {
                $table->dropForeign(['nurse_user_id']);
                $table->dropColumn('nurse_user_id');
            }
        });
    }
}
