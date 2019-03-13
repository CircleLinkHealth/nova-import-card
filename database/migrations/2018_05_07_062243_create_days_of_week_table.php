<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDaysOfWeekTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('days_of_week');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('days_of_week', function (Blueprint $table) {
            $table->integer('id')->index();
            $table->string('name');
            $table->string('abbreviation', 2);
        });
    }
}
