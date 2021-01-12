<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class RenameEmrDirectables extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \DB::table('emr_direct_addresses')
            ->select('emrDirectable_type')
            ->groupBy('emrDirectable_type')
            ->pluck('emrDirectable_type')
            ->each(
                function ($type) {
                    \DB::table('emr_direct_addresses')
                        ->where('emrDirectable_type', $type)
                        ->update(
                            [
                                'emrDirectable_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
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
        \DB::table('emr_direct_addresses')
            ->select('emrDirectable_type')
            ->groupBy('emrDirectable_type')
            ->pluck('emrDirectable_type')
            ->each(
                function ($type) {
                    \DB::table('emr_direct_addresses')
                        ->where('emrDirectable_type', $type)
                        ->update(
                            [
                                'emrDirectable_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
                            ]
                        );
                }
            );
    }
}
