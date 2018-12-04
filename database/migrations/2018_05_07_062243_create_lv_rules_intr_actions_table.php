<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvRulesIntrActionsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_rules_intr_actions');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lv_rules_intr_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rule_id')->unsigned();
            $table->integer('action_id')->unsigned();
            $table->integer('operator_id')->unsigned();
            $table->string('value', 100);
            $table->integer('created_by')->unsigned();
            $table->integer('modified_by')->unsigned();
            $table->timestamps();
        });
    }
}
