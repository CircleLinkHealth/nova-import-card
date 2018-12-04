<?php

use Illuminate\Database\Migrations\Migration;

class UpdateRolesAndPermissions1536945486 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! app()->environment(['testing'])) {
            Artisan::call('db:seed', [
                '--class' => 'RequiredRolesPermissionsSeeder',
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
