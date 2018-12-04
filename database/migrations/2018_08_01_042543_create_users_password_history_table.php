<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersPasswordHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_password_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');

            $table->string('older_password')->nullable();
            $table->string('old_password')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });

        \App\User::chunk(200, function ($users) {
            foreach ($users as $user) {
                $model = new \App\UserPasswordsHistory();
                $model->user_id = $user->id;
                $model->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_password_history');
    }
}
