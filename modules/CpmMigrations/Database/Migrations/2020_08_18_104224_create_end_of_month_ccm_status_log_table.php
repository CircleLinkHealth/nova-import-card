<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEndOfMonthCcmStatusLogTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('end_of_month_ccm_status_log');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('end_of_month_ccm_status_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('patient_user_id');
            $table->date('chargeable_month');
            $table->string('closed_ccm_status');

            $table->timestamps();

            $table->foreign('patient_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
}
