<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFaxLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('fax_logs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('fax_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('from');
            $table->string('to');
            $table->string('type');
            $table->boolean('delivered');
            $table->string('message');
            $table->timestamps();
        });
    }
}
