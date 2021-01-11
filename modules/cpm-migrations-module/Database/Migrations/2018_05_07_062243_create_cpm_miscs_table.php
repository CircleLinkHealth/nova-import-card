<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmMiscsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_miscs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_miscs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('details_care_item_id')->unsigned()->nullable();
            $table->integer('care_item_id')->unsigned()->nullable()->index('cpm_miscs_care_item_id_foreign');
            $table->string('name')->unique();
            $table->timestamps();
        });
    }
}
