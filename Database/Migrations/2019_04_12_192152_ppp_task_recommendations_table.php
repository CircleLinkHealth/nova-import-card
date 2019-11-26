<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PppTaskRecommendationsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ppp_task_recommendations');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppp_task_recommendations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->json('rec_task_titles');
            $table->string('codes')->nullable();
            $table->json('data');
            $table->timestamps();
        });
    }
}
