<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRolesAndPermissions1550823066 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! app()->environment(['testing'])) {
            Artisan::call('db:seed', [
                '--class' => 'CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder',
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