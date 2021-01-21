<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLvRoleUserTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_role_user');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'lv_role_user',
            function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('role_id')->unsigned()->index('lv_role_user_role_id_foreign');
                $table->primary(['user_id', 'role_id']);
            }
        );
    }
}
