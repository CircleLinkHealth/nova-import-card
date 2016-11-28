<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarePlanTemplatesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
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


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('care_plan_templates');
    }

}
