<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoiceCallsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voice_calls');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voice_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('call_id')->nullable(true);
            $table->unsignedInteger('voice_callable_id');
            $table->string('voice_callable_type');
            $table->timestamps();

            $table->foreign('call_id')
                ->references('id')
                ->on('calls')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
        });
    }
}
