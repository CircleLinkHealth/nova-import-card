<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
                '--class' => 'CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder',
            ]);
        }
    }
}
