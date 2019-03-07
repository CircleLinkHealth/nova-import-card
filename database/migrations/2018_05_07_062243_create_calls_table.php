<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCallsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('calls');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('note_id')->unsigned()->nullable()->index('calls_note_id_foreign');
            $table->text('service', 65535)->nullable();
            $table->text('status', 65535)->nullable();
            $table->text('inbound_phone_number', 65535)->nullable();
            $table->text('outbound_phone_number', 65535)->nullable();
            $table->integer('inbound_cpm_id')->unsigned()->index('calls_inbound_cpm_id_foreign');
            $table->integer('outbound_cpm_id')->unsigned()->nullable()->index('calls_outbound_cpm_id_foreign');
            $table->integer('call_time')->nullable();
            $table->timestamps();
            $table->boolean('is_cpm_outbound')->nullable();
            $table->text('window_start', 65535)->nullable();
            $table->text('window_end', 65535)->nullable();
            $table->date('scheduled_date')->nullable();
            $table->dateTime('called_date')->nullable();
            $table->string('attempt_note')->nullable();
            $table->text('scheduler', 65535)->nullable();
        });
    }
}
