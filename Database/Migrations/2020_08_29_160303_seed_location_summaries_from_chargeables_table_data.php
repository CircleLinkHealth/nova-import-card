<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class SeedLocationSummariesFromChargeablesTableData extends Migration
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
        Artisan::call('billing:migrate-all-location-services', [
            'month' => '2020-08-01',
        ]);
    }
}
