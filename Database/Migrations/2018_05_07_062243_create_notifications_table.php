<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('notifications');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'notifications',
            function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('type');
                $table->integer('notifiable_id')->unsigned();
                $table->string('notifiable_type');
                $table->integer('attachment_id')->unsigned()->nullable();
                $table->string('attachment_type')->nullable();
                $table->text('data', 65535)->nullable();
                $table->dateTime('read_at')->nullable();
                $table->timestamps();
                $table->index(['notifiable_id', 'notifiable_type']);
                $table->index(['attachment_id', 'attachment_type']);
            }
        );
    }
}
