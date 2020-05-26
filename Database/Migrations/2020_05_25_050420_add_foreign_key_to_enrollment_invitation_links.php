<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToEnrollmentInvitationLinks extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollables_invitation_links', function (Blueprint $table) {
            $table->dropColumn('batch_id');
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
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')
                ->references('id')
                ->on('enrollment_invitations_batches');
        });
    }
}
