<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateForeignIdsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('foreign_ids');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('foreign_ids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('location_id')->unsigned()->nullable()->index('location_foreign');
            $table->string('foreign_id');
            $table->string('system');
            $table->timestamps();
            $table->unique(['user_id', 'foreign_id', 'system'], 'unique_triple');
        });
    }
}
