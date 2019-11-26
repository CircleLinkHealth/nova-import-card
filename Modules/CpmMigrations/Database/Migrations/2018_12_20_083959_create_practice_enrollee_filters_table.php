<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePracticeEnrolleeFiltersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('practice_enrollee_filters');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('practice_enrollee_filters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('practice_id');
            $table->unsignedInteger('filter_id');
            $table->boolean('include')->default(0);
            $table->timestamps();

            $table->foreign('practice_id')
                ->references('id')
                ->on('practices')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('filter_id')
                ->references('id')
                ->on('enrollee_custom_filters')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }
}
