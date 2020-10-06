<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder;

class UpdateRolesAndPermissions1601985158 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!isUnitTestingEnv()) {
            Artisan::call('db:seed', [
                '--class' => \CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder::class,
            ]);
        }
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


