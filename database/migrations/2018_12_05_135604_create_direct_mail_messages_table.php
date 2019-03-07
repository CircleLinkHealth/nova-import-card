<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectMailMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('direct_mail_messages');
    }
}
