<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SoftwareOnlySeeders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!app()->environment(['testing'])) {
            Artisan::call('db:seed', [
                '--class' => 'CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder',
            ]);
            Artisan::call('db:seed', [
                '--class' => 'ChargeableServiceSeeder',
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
