<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ChangeContactsTableNamespace extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \DB::table('contacts')
            ->select('contactable_type')
            ->groupBy('contactable_type')
            ->pluck('contactable_type')
            ->each(
                function ($type) {
                    \DB::table('contacts')
                        ->where('contactable_type', $type)
                        ->update(
                            [
                                'contactable_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
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
        \DB::table('contacts')
            ->select('contactable_type')
            ->groupBy('contactable_type')
            ->pluck('contactable_type')
            ->each(
                function ($type) {
                    \DB::table('contacts')
                        ->where('contactable_type', $type)
                        ->update(
                            [
                                'contactable_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
                            ]
                        );
                }
            );
    }
}
