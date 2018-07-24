<?php

use Illuminate\Database\Migrations\Migration;

class ChangeCareCenterDisplayName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = \App\Role::where('name', 'care-center')->first();

        if ($role) {
            $role->display_name = 'Care Coach';
            $role->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $role = \App\Role::where('name', 'care-center')->first();

        $role->display_name = 'Care Center';
        $role->save();
    }
}
