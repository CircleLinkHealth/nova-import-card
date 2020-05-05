<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkWeekStartInWorkHours extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('work_hours', function (Blueprint $table) {
            $table->dropColumn('work_week_start');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('work_hours', function (Blueprint $table) {
            $table->date('work_week_start');
        });

        try {
            Artisan::call('command:createCalendarRecurringEventsForPastWindows');
        } catch (Exception $e) {
            Log::warning($e->getMessage());
        }
    }
}
