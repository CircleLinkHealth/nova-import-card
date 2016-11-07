<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('note_id');

            //What we use as a call service, hangouts
            $table->text('service');

            //Check whether the call connected/voice mail/ not answered etc
            $table->text('status');

            $table->text('inbound_phone_number');
            $table->text('outbound_phone_number');

            $table->unsignedInteger('inbound_cpm_id');
            $table->unsignedInteger('outbound_cpm_id');

            //in seconds
            $table->integer('call_time')->nullable();

            $table->foreign('inbound_cpm_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('outbound_cpm_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('note_id')
            ->references('id')
            ->on('notes')
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
        Schema::drop('calls');
    }
}
