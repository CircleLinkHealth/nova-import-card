<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class DeleteCalendarMigrationEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $migation1 = DB::table('migrations')->where('migration', '2019_11_15_041401_add_calendar_fields_to_nurse_contact_table')->delete();
        $migation2 = DB::table('migrations')->where('migration', '2019_11_18_163004_add_work_week_start_in_work_hours')->delete();
    }
}
