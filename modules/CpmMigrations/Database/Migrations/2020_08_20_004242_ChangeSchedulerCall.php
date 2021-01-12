<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSchedulerCall extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->string('scheduler')->change();
            $table->index(['type', 'status', 'scheduled_date']);
        });

        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->index(['id', 'is_monitored', 'patient_id']);
        });
    }
}
