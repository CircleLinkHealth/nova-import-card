<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCareAmbassadorsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('care_ambassadors');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'care_ambassadors',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('care_ambassadors_user_id_foreign');
                $table->integer('hourly_rate')->unsigned()->nullable();
                $table->boolean('speaks_spanish');
                $table->timestamps();
            }
        );
    }
}
