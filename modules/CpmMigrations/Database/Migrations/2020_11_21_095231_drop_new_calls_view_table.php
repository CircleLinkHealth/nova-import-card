<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class DropNewCallsViewTable extends Migration
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
        \DB::statement('DROP VIEW IF EXISTS calls_view');
        \DB::statement('DROP VIEW IF EXISTS calls_view_to_deprecate');
    }
}
