<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePatientIdToUserIdInProviderReportsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_reports', function (Blueprint $table) {
            if (Schema::hasColumn('provider_reports', 'user_id')) {
                $table->renameColumn('user_id', 'patient_id');
            } else {
                $table->unsignedInteger('patient_id')
                    ->after('id');
            }

            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->dropForeign(['hra_instance_id']);
            $table->dropForeign(['vitals_instance_id']);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_reports', function (Blueprint $table) {
            if (Schema::hasColumn('provider_reports', 'patient_id')) {
                $table->renameColumn('patient_id', 'user_id');
            } else {
                $table->unsignedInteger('user_id')
                    ->after('id');
            }

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('hra_instance_id')
                ->references('id')
                ->on('survey_instances')
                ->onUpdate('cascade');

            $table->foreign('vitals_instance_id')
                ->references('id')
                ->on('survey_instances')
                ->onUpdate('cascade');
        });
    }
}
