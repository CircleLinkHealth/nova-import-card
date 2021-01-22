<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ChangeChargeablesTableNamespace extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \DB::table('chargeables')
            ->select('chargeable_type')
            ->groupBy('chargeable_type')
            ->pluck('chargeable_type')
            ->each(
                function ($type) {
                    \DB::table('chargeables')
                        ->where('chargeable_type', $type)
                        ->update(
                            [
                                'chargeable_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
                            ]
                        );
                }
            );
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        \DB::table('chargeables')
            ->select('chargeable_type')
            ->groupBy('chargeable_type')
            ->pluck('chargeable_type')
            ->each(
                function ($type) {
                    \DB::table('chargeables')
                        ->where('chargeable_type', $type)
                        ->update(
                            [
                                'chargeable_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
                            ]
                        );
                }
            );
    }
}
