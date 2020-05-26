<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\EnrollmentInvitationsBatch;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
        CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink::whereNull('batch_id')->chunk(100, function ($links) {
            foreach ($links as $link) {
                if (User::class === $link->enrollable_type) {
                    $practiceId = User::find($link->invitationable_id)->program_id;
                } elseif (Enrollee::class === $link->enrollable_type) {
                    $practiceId = Enrollee::find($link->invitationable_id);
                }

                if ( ! isset($practiceId)) {
                    if (app()->environment('production')) {
                        continue;
                    }

                    $link->delete();
                    continue;
                }

                $batch = EnrollmentInvitationsBatch::firstOrCreateAndRemember($practiceId, "Initial:{$link->button_color}", 2);

                $link->batch_id = $batch->id;
                $link->save();
            }
        });

        Schema::table('enrollables_invitation_links', function (Blueprint $table) {
            if ( ! Schema::hasColumn('enrollables_invitation_links', 'batch_id')) {
                $table->unsignedInteger('batch_id');
            }

            $table->foreign('batch_id')
                ->references('id')
                ->on('enrollment_invitations_batches');
        });
    }
}
