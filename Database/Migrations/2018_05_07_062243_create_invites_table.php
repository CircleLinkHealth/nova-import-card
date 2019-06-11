<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('invites');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'invites',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('inviter_id')->unsigned()->index('invites_inviter_id_foreign');
                $table->integer('role_id')->unsigned()->nullable()->index('invites_role_id_foreign');
                $table->string('email');
                $table->string('subject')->nullable();
                $table->string('message')->nullable();
                $table->string('code')->nullable();
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }
}
