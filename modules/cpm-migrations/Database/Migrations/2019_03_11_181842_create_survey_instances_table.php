<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyInstancesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('survey_instances');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('survey_id');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            /* $table->foreign('survey_id')
                   ->references('id')->on('surveys')
                   ->onDelete('cascade');*/
        });
    }
}
