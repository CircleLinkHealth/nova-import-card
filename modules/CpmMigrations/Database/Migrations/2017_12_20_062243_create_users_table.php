<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('users');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('saas_account_id')->unsigned()->nullable()->index('users_saas_account_id_foreign');
            $table->boolean('skip_browser_checks')->default(0)->comment('Skip compatible browser checks when the user logs in');
            $table->boolean('count_ccm_time')->default(0);
            $table->string('username', 60)->index('user_login_key')->nullable();
            $table->integer('program_id')->unsigned()->nullable();
            $table->string('password', 60)->nullable();
            $table->string('email', 100);
            $table->dateTime('user_registered')->nullable();
            $table->integer('user_status')->default(0);
            $table->boolean('auto_attach_programs')->default(false)->nullable();
            $table->string('display_name', 250)->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('timezone')->nullable()->default('America/New_York');
            $table->string('status')->nullable();
            $table->boolean('access_disabled')->default(false);

            $table->boolean('is_auto_generated')->default(false);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_online')->default(0);
        });
    }
}
