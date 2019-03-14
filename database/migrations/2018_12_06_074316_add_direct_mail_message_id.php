<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDirectMailMessageId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'ccdas',
            function (Blueprint $table) {
                $table->unsignedInteger('direct_mail_message_id')
                      ->nullable()
                      ->after('id');
                
                $table->foreign('direct_mail_message_id')
                      ->references('id')
                      ->on('direct_mail_messages')
                      ->onUpdate('cascade')
                      ->onDelete('cascade');
            }
        );
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'ccdas',
            function (Blueprint $table) {
                //
            }
        );
    }
}
