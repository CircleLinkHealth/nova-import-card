<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddWorkWeekStartInWorkHours extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(
            'work_hours',
            function (Blueprint $table) {
                $table->dropColumn('work_week_start');
            }
        );
    }
    
    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('work_hours', 'work_week_start')) {
            Schema::table(
                'work_hours',
                function (Blueprint $table) {
                    $table->date('work_week_start');
                }
            );
            Artisan::call('command:createCalendarRecurringEventsForPastWindows');
        }
    
    }
}
