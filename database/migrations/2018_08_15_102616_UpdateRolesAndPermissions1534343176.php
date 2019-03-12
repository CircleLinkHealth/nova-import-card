<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class UpdateRolesAndPermissions1534343176 extends Migration
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
        Artisan::call('db:seed', [
            '--class' => 'RequiredRolesPermissionsSeeder',
        ]);
    }
}
