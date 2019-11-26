<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDirectMailMessageId extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(
            'ccdas',
            function (Blueprint $table) {
            }
        );
    }

    /**
     * Run the migrations.
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
}
