<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHolidaysTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('holidays');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'holidays',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('nurse_info_id')->unsigned()->index('holidays_nurse_info_id_foreign');
                $table->date('date');
                $table->timestamps();
            }
        );
    }
}
