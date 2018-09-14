<?php

use App\Permission;
use App\User;
use Illuminate\Database\Migrations\Migration;

class GrantRegisterredNursesFromPractice169CareplanApproveRights extends Migration
{
    /**
     * We're dropping feature that allows RN's to approve CarePlans, in favor of directly granting permission to Users
     * to approve CPs. Practice with id 169 is the only practice that uses this. This migration will grant permission
     * 'care-plan-approve' to all users with role 'registered-nurse' that belong to practice 169.
     *
     * @return void
     */
    public function up()
    {
        $careplanApprove = Permission::where('name', 'care-plan-approve')->first();

        User::ofType('registered-nurse')
            ->ofPractice(169)
            ->get()
            ->each(function ($rn) use ($careplanApprove) {
                $rn->attachPermission($careplanApprove->id);
            });
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
