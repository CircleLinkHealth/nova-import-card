<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnrolleesRequestInfo extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollees_request_info', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('enrollable_id');
            $table->string('enrollable_type');
            $table->timestamps();
        });
    }
}