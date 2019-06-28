<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectMailMessagesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('direct_mail_messages');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('direct_mail_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message_id');
            $table->string('from');
            $table->string('to');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->unsignedInteger('num_attachments')->nullable();
            $table->timestamps();
        });
    }
}
