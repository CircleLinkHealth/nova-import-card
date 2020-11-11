<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRpmProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpm_problems');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpm_problems', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('practice_id');

            $table->string('code_type');
            $table->string('code');
            $table->string('description');

            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('cascade');

            $table->timestamps();
        });
    }
}
