<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPasswordHistoryTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users_password_history');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'users_password_history',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');

                $table->string('older_password')->nullable();
                $table->string('old_password')->nullable();
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            }
        );

        \CircleLinkHealth\Customer\Entities\User::chunk(
            200,
            function ($users) {
                foreach ($users as $user) {
                    $model = new \CircleLinkHealth\Customer\Entities\UserPasswordsHistory();
                    $model->user_id = $user->id;
                    $model->save();
                }
            }
        );
    }
}
