<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInstructablesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('instructables');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('instructables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpm_instruction_id')->unsigned()->index('instructables_cpm_instruction_id_foreign');
            $table->integer('instructable_id')->unsigned();
            $table->string('instructable_type');
            $table->timestamps();
        });
    }
}
