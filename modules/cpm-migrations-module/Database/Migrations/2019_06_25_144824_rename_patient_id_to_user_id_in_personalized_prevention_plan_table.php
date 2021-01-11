<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePatientIdToUserIdInPersonalizedPreventionPlanTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personalized_prevention_plan', function (Blueprint $table) {
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
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personalized_prevention_plan', function (Blueprint $table) {
            if (Schema::hasColumn('personalized_prevention_plan', 'patient_id')) {
                $table->renameColumn('patient_id', 'user_id');
            } elseif ( ! Schema::hasColumn('personalized_prevention_plan', 'user_id')) {
                $table->unsignedInteger('user_id')
                    ->after('id');

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            }
        });
    }
}
