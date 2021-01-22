<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMaResultsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ma_results');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ma_results', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('blog_id')->nullable()->default(3)->index('blog_id');
            $table->integer('resultid')->nullable()->index('resultid');
            $table->string('user', 100)->nullable();
            $table->string('uid', 45)->nullable()->index('uid');
            $table->dateTime('DTS')->nullable();
            $table->integer('program')->nullable();
            $table->string('type', 45)->nullable();
            $table->string('key')->nullable();
            $table->string('value', 1000)->nullable();
            $table->string('action', 45)->nullable();
            $table->string('result_type', 45)->nullable();
        });
    }
}
