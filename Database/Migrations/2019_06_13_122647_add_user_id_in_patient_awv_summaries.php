<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdInPatientAwvSummaries extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('patient_awv_summaries', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->renameColumn('user_id', 'patient_id');
            } else {
                $table->unsignedInteger('patient_id')
                    ->after('id');
            }

            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('patient_awv_summaries', 'patient_id')) {
                $table->dropForeign(['patient_id']);
                $table->renameColumn('patient_id', 'user_id');
            } else {
                $table->unsignedInteger('user_id')
                    ->after('id');
            }

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
}
