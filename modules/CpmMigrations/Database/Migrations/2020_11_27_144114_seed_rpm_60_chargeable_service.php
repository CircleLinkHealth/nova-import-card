<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Migrations\Migration;

class SeedRpm60ChargeableService extends Migration
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
        ChargeableService::whereCode('CPT 99458')
            ->update([
                'code' => ChargeableService::RPM40,
            ]);

        Artisan::call('billing:seed-chargeable-services');
    }
}
