<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class RefactorPermissiblesPermissibleType extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \DB::table('permissibles')
            ->select('permissible_type')
            ->groupBy('permissible_type')
            ->pluck('permissible_type')
            ->each(
                function ($type) {
                    \DB::table('permissibles')
                        ->where('permissible_type', $type)
                        ->update(
                            [
                                'permissible_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
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
        \DB::table('permissibles')
            ->select('permissible_type')
            ->groupBy('permissible_type')
            ->pluck('permissible_type')
            ->each(
                function ($type) {
                    \DB::table('permissibles')
                        ->where('permissible_type', $type)
                        ->update(
                            [
                                'permissible_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
                            ]
                        );
                }
            );
    }
}
