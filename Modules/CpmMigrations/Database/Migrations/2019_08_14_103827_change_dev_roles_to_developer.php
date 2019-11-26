<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Migrations\Migration;

class ChangeDevRolesToDeveloper extends Migration
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
        if ( ! isProductionEnv()) {
            return;
        }
        $userIds = [
            //Antonis
            12489,
            //Constantinos
            8935,
            //Michalis
            357,
            //Pangratios
            9309,
        ];

        //clear all existing roles for all practices
        DB::table('practice_role_user')
            ->whereIn('user_id', $userIds)
            ->delete();

        //get Demo Practice and Developer Role
        $demo = Practice::where('name', 'demo')
            ->first();

        $developerRole = Role::where('name', 'developer')
            ->first();

        User::findMany($userIds)
            ->each(function (User $u) use ($demo, $developerRole) {
                $u->attachRoleForPractice($developerRole, $demo->id);
            });
    }
}
