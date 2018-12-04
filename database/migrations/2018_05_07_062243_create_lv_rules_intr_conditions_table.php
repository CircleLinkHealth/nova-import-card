<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvRulesIntrConditionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_rules_intr_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rule_id')->unsigned();
            $table->integer('condition_id')->unsigned();
            $table->string('operator_id');
            $table->string('value', 100);
            $table->string('value_b');
            $table->integer('created_by')->unsigned();
            $table->integer('modified_by')->unsigned();
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
        Schema::drop('lv_rules_intr_conditions');
    }
}
