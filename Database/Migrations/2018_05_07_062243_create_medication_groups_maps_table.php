<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMedicationGroupsMapsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('medication_groups_maps');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('medication_groups_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('keyword');
            $table->integer('medication_group_id')->unsigned()->index('medication_groups_maps_medication_group_id_foreign');
            $table->timestamps();
        });
    }
}
