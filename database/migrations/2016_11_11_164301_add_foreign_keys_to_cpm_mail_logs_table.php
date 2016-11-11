<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmMailLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_mail_logs', function (Blueprint $table) {
            $table->foreign('note_id')->references('id')->on('notes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('receiver_cpm_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('sender_cpm_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_mail_logs', function (Blueprint $table) {
            $table->dropForeign('cpm_mail_logs_note_id_foreign');
            $table->dropForeign('cpm_mail_logs_receiver_cpm_id_foreign');
            $table->dropForeign('cpm_mail_logs_sender_cpm_id_foreign');
        });
    }

}
