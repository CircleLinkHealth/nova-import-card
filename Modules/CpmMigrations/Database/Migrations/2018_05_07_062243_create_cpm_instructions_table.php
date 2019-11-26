<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmInstructionsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_instructions');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_instructions', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_default')->default(0);
            $table->text('name', 65535);
            $table->timestamps();
        });
    }
}
