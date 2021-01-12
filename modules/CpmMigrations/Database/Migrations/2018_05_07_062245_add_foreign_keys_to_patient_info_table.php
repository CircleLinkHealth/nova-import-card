<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            tryDropForeignKey($table, 'patient_info_care_plan_id_foreign');
            tryDropForeignKey($table, 'patient_info_family_id_foreign');
            tryDropForeignKey($table, 'patient_info_imported_medical_record_id_foreign');
            tryDropForeignKey($table, 'patient_info_next_call_id_foreign');
            tryDropForeignKey($table, 'patient_info_user_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->foreign('care_plan_id')
                ->references('id')
                ->on('care_plans')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');

            $table->foreign('family_id')
                ->references('id')
                ->on('families')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');

            $table->foreign('imported_medical_record_id')
                ->references('id')
                ->on('imported_medical_records')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');

            $table->foreign('next_call_id')
                ->references('id')
                ->on('calls')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }
}
