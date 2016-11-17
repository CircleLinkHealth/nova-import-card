<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvRulesConditionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_rules_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('condition_name', 50);
            $table->string('condition', 50)->nullable();
            $table->string('condition_description', 200)->nullable();
            $table->string('active', 100)->default('Y');
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
        Schema::drop('lv_rules_conditions');
    }

}
