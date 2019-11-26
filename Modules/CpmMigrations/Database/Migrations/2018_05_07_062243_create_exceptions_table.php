<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExceptionsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('exceptions');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('exceptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class');
            $table->string('file');
            $table->integer('code');
            $table->integer('status_code')->default(0);
            $table->integer('line');
            $table->text('message', 65535);
            $table->text('trace', 16777215);
            $table->timestamps();
            $table->integer('user_id')->nullable();
            $table->text('data', 65535)->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
        });
    }
}
