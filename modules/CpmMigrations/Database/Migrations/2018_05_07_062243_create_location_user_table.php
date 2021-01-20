<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationUserTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('location_user');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'location_user',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('location_id')->unsigned()->index('location_user_location_id_foreign');
                $table->integer('user_id')->unsigned()->index('location_user_user_id_foreign');
                $table->timestamps();
                $table->unique(['location_id', 'user_id']);
            }
        );
    }
}
