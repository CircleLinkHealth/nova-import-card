<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvRulesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_rules');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lv_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rule_name', 50);
            $table->string('rule_description', 200)->nullable();
            $table->string('active', 1)->default('N');
            $table->string('type_id', 10)->nullable();
            $table->integer('sort')->unsigned();
            $table->dateTime('effective_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->text('summary', 65535)->nullable();
            $table->string('approve', 1)->default('N');
            $table->string('archive', 1)->default('N');
            $table->integer('created_by')->unsigned();
            $table->integer('modified_by')->unsigned();
            $table->timestamps();
        });
    }
}
