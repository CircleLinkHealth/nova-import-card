<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\ChargeableService;
use Illuminate\Database\Migrations\Migration;

class AddAwvChargeableServicesToChargeableServicesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        ChargeableService::whereIn('code', ['AWV: G0438', 'AWV: G0439'])->delete();
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        //using create to add timestamps
        ChargeableService::create(
           [
               'code'        => 'AWV: G0438',
               'description' => 'Initial Annual Wellness Visit',
           ]
       );

        ChargeableService::create([
            'code'        => 'AWV: G0439',
            'description' => 'Subsequent Annual Wellness Visit',
        ]);
    }
}
