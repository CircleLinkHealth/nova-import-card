<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCcmPlusCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \CircleLinkHealth\Customer\Entities\ChargeableService::where('code', 'G2058(>40mins)')
            ->update([
                'code' => \CircleLinkHealth\Customer\Entities\ChargeableService::CCM_PLUS_40
            ]);

        \CircleLinkHealth\Customer\Entities\ChargeableService::where('code', 'G2058(>60mins)')
                                                             ->update([
                                                                 'code' => \CircleLinkHealth\Customer\Entities\ChargeableService::CCM_PLUS_60
                                                             ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
