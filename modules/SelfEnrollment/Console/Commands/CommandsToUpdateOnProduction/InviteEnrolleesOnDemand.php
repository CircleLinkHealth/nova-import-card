<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction;

use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationsBatch;
use CircleLinkHealth\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class InviteEnrolleesOnDemand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mass Invite Self Enrollment Enrollment Enrollees with manually marked batch.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:self-enrollment-invitations-for {practiceId} {limit} {enrolleeIds?*}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $enrolleeIds       = $this->argument('enrolleeIds');
        $limit             = $this->argument('limit');
        $manualInviteBatch = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE;

        Enrollee::with('user')
            ->whereNotNull('user_id')
            ->where('practice_id', $this->argument('practiceId'))
            ->canSendSelfEnrollmentInvitation(true)
            ->whereNotNull('provider_id')
            ->when( ! empty($enrolleeIds), function ($enrollees) use ($enrolleeIds) {
                $enrollees->whereIn('id', $enrolleeIds);
            })
            ->limit(intval($limit))
            ->chunk(100, function ($enrollees) use ($manualInviteBatch) {
                $enrollees->each(function ($enrollee) use ($manualInviteBatch) {
                    $this->warn("Inviting enrollee {$enrollee->id}");
                    $invitationBatch = EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                        $enrollee->practice_id,
                        $manualInviteBatch
                    );
                    SendInvitation::dispatch($enrollee->user, $invitationBatch->id);
                });
            });
    }
}
