<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvitesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('inviter_id');
            $table->foreign('inviter_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedInteger('role_id')
                ->nullable();
            $table->foreign('role_id')
                ->references('id')
                ->on('lv_roles')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->string('email');
            $table->string('subject')->nullable();
            $table->string('message')->nullable();

            $table->string('code')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invites');
    }

}
