<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWorkHoursTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('work_hours');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('work_hours', function (Blueprint $table) {
            $table->increments('id');
            $table->string('workhourable_type');
            $table->integer('workhourable_id')->unsigned();
            $table->integer('monday')->unsigned()->default(0);
            $table->integer('tuesday')->unsigned()->default(0);
            $table->integer('wednesday')->unsigned()->default(0);
            $table->integer('thursday')->unsigned()->default(0);
            $table->integer('friday')->unsigned()->default(0);
            $table->integer('saturday')->unsigned()->default(0);
            $table->integer('sunday')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
