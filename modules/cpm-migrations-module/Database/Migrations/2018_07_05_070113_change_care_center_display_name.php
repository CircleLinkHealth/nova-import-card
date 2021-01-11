<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ChangeCareCenterDisplayName extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $role = \CircleLinkHealth\Customer\Entities\Role::where('name', 'care-center')->first();

        $role->display_name = 'Care Center';
        $role->save();
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $role = \CircleLinkHealth\Customer\Entities\Role::where('name', 'care-center')->first();

        if ($role) {
            $role->display_name = 'Care Coach';
            $role->save();
        }
    }
}
