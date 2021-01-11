<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToInvitesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropForeign('invites_inviter_id_foreign');
            $table->dropForeign('invites_role_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->foreign('inviter_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('role_id')->references('id')->on('lv_roles')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }
}
