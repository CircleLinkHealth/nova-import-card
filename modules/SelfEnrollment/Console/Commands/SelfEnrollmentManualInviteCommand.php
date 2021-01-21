<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationsBatch;
use CircleLinkHealth\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class SelfEnrollmentManualInviteCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a Self Enrollment Invite to an Enrollee. If the Enrollee does not have a User ID, it will throw an Exception';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'self-enrollment:invite {enrolleeId}';

    public function handle()
    {
        $enrollee          = Enrollee::with('user.enrollee')->has('user')->whereNotNull('practice_id')->findOrFail($this->argument('enrolleeId'));
        $manualInviteBatch = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE;
        $invitationBatch   = EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            $manualInviteBatch
        );

        SendInvitation::dispatch($enrollee->user, $invitationBatch->id);
    }
}
