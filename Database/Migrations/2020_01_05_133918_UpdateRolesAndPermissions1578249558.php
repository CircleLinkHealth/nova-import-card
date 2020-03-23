<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRolesAndPermissions1578249558 extends Migration
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
        if ( ! isUnitTestingEnv()) {
            Artisan::call('db:seed', [
                '--class' => \CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder::class,
            ]);
        }
    }
}
