<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEhrsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ehrs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'ehrs',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('pdf_report_handler');
                $table->timestamps();
            }
        );
    }
}
