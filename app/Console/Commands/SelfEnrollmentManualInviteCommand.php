<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\SendSelfEnrollmentInvitation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $enrollee = Enrollee::with('user')->has('user')->findOrFail($this->argument('enrolleeId'));

        SendSelfEnrollmentInvitation::dispatch($enrollee->user);
    }
}
