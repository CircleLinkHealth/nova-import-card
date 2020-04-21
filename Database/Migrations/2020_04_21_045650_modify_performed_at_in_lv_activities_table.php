<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ModifyPerformedAtInLvActivitiesTable extends Migration
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
        DB::statement('ALTER TABLE lv_activities CHANGE performed_at performed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE lv_activities CHANGE performed_at_gmt performed_at_gmt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }
}
