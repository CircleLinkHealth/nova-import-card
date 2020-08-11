<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpmCallAlertsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cpm_call_alerts');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_call_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('call_id')->nullable(true)->unique();
            $table->boolean('resolved')->default(false);
            $table->string('comment')->nullable(true)->default(null);
            $table->timestamps();

            $table->foreign('call_id')
                ->references('id')
                ->on('calls')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
        });
    }
}
