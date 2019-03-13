<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPracticeRoleUserTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('practice_role_user', function (Blueprint $table) {
            $table->dropForeign('practice_user_role_id_foreign');
            $table->dropForeign('practice_user_user_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('practice_role_user', function (Blueprint $table) {
            $table->foreign('role_id', 'practice_user_role_id_foreign')->references('id')->on('lv_roles')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('user_id', 'practice_user_user_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
