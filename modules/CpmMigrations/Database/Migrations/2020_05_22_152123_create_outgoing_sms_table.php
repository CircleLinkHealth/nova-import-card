<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingSmsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoing_sms');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('sender_user_id');
            $table->string('receiver_phone_number', 12);
            $table->text('message');

            $table->timestamps();

            $table->foreign('sender_user_id')->references('id')->on('users')->onUpdate('cascade');
        });
    }
}
