<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSurveyInstanceForeignKeysToPppTable extends Migration
{
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

}
