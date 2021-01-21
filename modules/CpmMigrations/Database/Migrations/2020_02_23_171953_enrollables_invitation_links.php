<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnrollablesInvitationLinks extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollables_invitation_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invitationable_id');
            $table->string('invitationable_type');
            $table->string('link_token');
            $table->boolean('manually_expired');
            $table->string('url');
            $table->timestamps();
        });
    }
}