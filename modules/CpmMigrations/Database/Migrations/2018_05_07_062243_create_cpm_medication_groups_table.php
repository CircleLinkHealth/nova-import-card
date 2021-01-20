<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmMedicationGroupsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_medication_groups');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_medication_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('care_item_id')->unsigned()->nullable()->index('cpm_medication_groups_care_item_id_foreign');
            $table->string('name')->unique();
            $table->timestamps();
        });
    }
}