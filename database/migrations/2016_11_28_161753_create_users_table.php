<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 60)->index('user_login_key');
            $table->string('program_id', 10);
            $table->string('password', 60);
            $table->string('email', 100);
            $table->dateTime('user_registered')->default('0000-00-00 00:00:00');
            $table->integer('user_status')->default(0);
            $table->boolean('auto_attach_programs');
            $table->string('display_name', 250);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('address2');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('timezone')->nullable();
            $table->string('status');
            $table->boolean('access_disabled');
            $table->boolean('is_auto_generated')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_online')->default(0);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }

}
