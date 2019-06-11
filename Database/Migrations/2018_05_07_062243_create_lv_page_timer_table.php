<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLvPageTimerTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_page_timer');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'lv_page_timer',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('billable_duration')->unsigned();
                $table->integer('duration')->unsigned();
                $table->string('duration_unit', 30)->nullable();
                $table->integer('patient_id')->unsigned();
                $table->integer('provider_id')->unsigned();
                $table->dateTime('start_time')->nullable();
                $table->dateTime('end_time')->nullable();
                $table->string('redirect_to')->nullable();
                $table->string('url_full', 200)->nullable();
                $table->string('url_short', 200)->nullable();
                $table->string('activity_type');
                $table->string('title');
                $table->string('query_string')->nullable();
                $table->integer('program_id')->unsigned();
                $table->string('ip_addr', 200)->nullable();
                $table->timestamps();
                $table->string('processed', 10)->nullable();
                $table->string('rule_params')->nullable();
                $table->integer('rule_id')->unsigned()->nullable();
                $table->softDeletes();
                $table->string('user_agent')->nullable();
            }
        );
    }
}
