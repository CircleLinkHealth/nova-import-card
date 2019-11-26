<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarePlanTemplatesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('care_plan_templates');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('care_plan_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('display_name');
            $table->integer('program_id')->unsigned()->nullable()->index('care_plan_templates_program_id_foreign');
            $table->string('type')->unique();
            $table->timestamps();
        });
    }
}
