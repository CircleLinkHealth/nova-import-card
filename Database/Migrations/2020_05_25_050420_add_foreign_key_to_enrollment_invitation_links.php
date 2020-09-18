<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\EnrollmentInvitationsBatch;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
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
        if ( ! Schema::hasColumn('enrollables_invitation_links', 'batch_id')) {
            Schema::table('enrollables_invitation_links', function (Blueprint $table) {
                $table->unsignedInteger('batch_id');
            });
        }

        EnrollableInvitationLink::whereNull('batch_id')->orWhere('batch_id', '<', 1)->chunk(100, function ($links) {
            foreach ($links as $link) {
                if (User::class === $link->invitationable_type) {
                    $practiceId = User::find($link->invitationable_id)->program_id;
                } elseif (Enrollee::class === $link->invitationable_type) {
                    $practiceId = optional(Enrollee::find($link->invitationable_id))->practice_id;
                }

                if ( ! isset($practiceId)) {
                    if (app()->environment('production')) {
                        continue;
                    }

                    $link->delete();
                    continue;
                }

                $color = $link->button_color ?? SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
                $batch = EnrollmentInvitationsBatch::firstOrCreateAndRemember($practiceId, "Initial:$color", 2);

                $link->batch_id = $batch->id;
                $link->save();
            }
        });

        Schema::table('enrollables_invitation_links', function (Blueprint $table) {
            $table->foreign('batch_id')
                ->references('id')
                ->on('enrollment_invitations_batches');
        });
    }
}
