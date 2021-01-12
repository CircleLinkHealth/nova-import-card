<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrolleeCustomFiltersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('enrollee_custom_filters');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('enrollee_custom_filters', function (Blueprint $table) {
            $table->string('name');
            $table->string('type');
            $table->increments('id');
            $table->timestamps();
        });
    }
}
