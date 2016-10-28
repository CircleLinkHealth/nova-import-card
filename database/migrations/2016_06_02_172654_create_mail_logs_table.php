<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateMailLogsTable extends Migration
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

            $table->text('sender_email');
            $table->text('receiver_email');

            $table->text('body');
            $table->text('subject');

            $table->text('type');

            $table->unsignedInteger('sender_cpm_id');
            $table->unsignedInteger('receiver_cpm_id');

            $table->foreign('sender_cpm_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('receiver_cpm_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::drop('cpm_mail_logs');

    }
}
