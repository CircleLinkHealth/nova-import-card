<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveyInstanceForeignKeysToPppTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personalized_prevention_plan', function (Blueprint $table) {
            $table->dropForeign(['hra_instance_id']);
            $table->dropForeign(['vitals_instance_id']);

            $table->dropColumn('hra_instance_id');
            $table->dropColumn('vitals_instance_id');
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
            $table->unsignedInteger('hra_instance_id')->after('user_id')->nullable();
            $table->unsignedInteger('vitals_instance_id')->after('user_id')->nullable();

            $table->foreign('hra_instance_id')
                ->references('id')->on('survey_instances')
                ->onDelete('set null');

            $table->foreign('vitals_instance_id')
                ->references('id')->on('survey_instances')
                ->onDelete('set null');
        });
    }
}
