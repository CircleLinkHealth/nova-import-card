<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

        Artisan::call('command:createCalendarRecurringEventsForPastWindows');
    }
}
