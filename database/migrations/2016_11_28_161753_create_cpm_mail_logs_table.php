<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmMailLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_mail_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('sender_email', 65535);
            $table->text('receiver_email', 65535);
            $table->text('body', 65535);
            $table->text('subject', 65535);
            $table->text('type', 65535);
            $table->integer('sender_cpm_id')->unsigned()->index('cpm_mail_logs_sender_cpm_id_foreign');
            $table->integer('receiver_cpm_id')->unsigned()->index('cpm_mail_logs_receiver_cpm_id_foreign');
            $table->timestamps();
            $table->integer('note_id')->unsigned()->nullable()->index('cpm_mail_logs_note_id_foreign');
            $table->dateTime('seen_on')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cpm_mail_logs');
    }

}
