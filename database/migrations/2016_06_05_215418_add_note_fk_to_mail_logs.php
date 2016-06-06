<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoteFkToMailLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_mail_logs', function (Blueprint $table) {


            $table->unsignedInteger('note_id')->nullable();

            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
            $table->dropForeign('note_id');
        });
    }
}
