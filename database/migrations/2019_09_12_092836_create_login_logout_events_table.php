<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginLogoutEventsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('login_logout_events');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('login_logout_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->dateTime('login_time')->nullable();
            $table->dateTime('logout_time')->nullable();
            $table->string('ip_address');
            $table->boolean('was_edited')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('provider_id')
                ->on('lv_page_timer');
        });
    }
}
