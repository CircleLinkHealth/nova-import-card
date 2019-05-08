<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

class UpdateRolesAndPermissions1555418953 extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! app()->environment(['testing'])) {
            Artisan::call('db:seed', [
                '--class' => RequiredRolesPermissionsSeeder::class,
            ]);
        }
    }
}
