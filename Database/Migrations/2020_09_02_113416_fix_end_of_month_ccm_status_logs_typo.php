<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class FixEndOfMonthCcmStatusLogsTypo extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $currentTableName = 'end_of_month_ccm_status_log';

        if (Schema::hasTable($currentTableName.'s')) {
            Schema::rename($currentTableName.'s', $currentTableName);
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currentTableName = 'end_of_month_ccm_status_log';
        
        if (Schema::hasTable($currentTableName)) {
            Schema::rename($currentTableName, $currentTableName.'s');
        }
    }
}
