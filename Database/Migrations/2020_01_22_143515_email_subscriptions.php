<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmailSubscriptions extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('unsubscriptions_notification_mail');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unsubscriptions_notification_mail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('user_id');
            $table->string('notification_type');
            $table->date('unsubscribed_at');
            $table->string('channel');
            $table->softDeletes();

            $table->timestamps();
        });
    }
}
