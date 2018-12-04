<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePracticeRoleUserTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('practice_role_user');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('practice_role_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->index('practice_user_user_id_foreign');
            $table->integer('program_id')->unsigned()->index('lv_program_user_program_id_foreign')->nullable();
            $table->integer('role_id')->unsigned()->nullable()->index('practice_user_role_id_foreign');
            $table->boolean('has_admin_rights')->default(false);
            $table->boolean('send_billing_reports')->default(false);
            $table->unique(['user_id', 'role_id', 'program_id'], 'practice_user_user_id_role_id_program_id_unique');
        });
    }
}
