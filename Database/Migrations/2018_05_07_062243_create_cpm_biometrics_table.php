<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmBiometricsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_biometrics');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_biometrics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('care_item_id')->unsigned()->nullable()->index('cpm_biometrics_care_item_id_foreign');
            $table->string('name')->unique();
            $table->integer('type')->unsigned()->nullable();
            $table->timestamps();
            $table->string('unit');
        });
    }
}
