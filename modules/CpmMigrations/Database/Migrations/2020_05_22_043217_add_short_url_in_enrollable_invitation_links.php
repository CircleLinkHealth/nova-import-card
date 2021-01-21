<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShortUrlInEnrollableInvitationLinks extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollables_invitation_links', function (Blueprint $table) {
            $table->dropColumn('short_url');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollables_invitation_links', function (Blueprint $table) {
            $table->string('short_url')->nullable();
        });
    }
}