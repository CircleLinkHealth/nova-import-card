<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvRulesOperatorsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_rules_operators');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lv_rules_operators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('operator', 50);
            $table->string('operator_description', 200)->nullable();
            $table->string('operation', 200);
            $table->integer('created_by')->unsigned();
            $table->integer('modified_by')->unsigned();
            $table->timestamps();
        });
    }
}
