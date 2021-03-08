<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SelfEnrollment\Console\Commands\CommandHelpers;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\SelfEnrollment\Jobs\SendReminder;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class SendSelfEnrollmentRemindersCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Self Enrollment reminders for given ids and practice:commonwealth';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:self-enrollment-reminders {practiceId} {enrolleeIds?*}';

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
        $ids =   $this->argument('enrolleeIds');
        $enrolleeIds = CommandHelpers::getEnrolleeIds($ids);

        Enrollee::with('user.patientInfo')
            ->where('practice_id', $this->argument('practiceId'))
            ->when($enrolleeIds, function ($enrollee) use ($enrolleeIds){
                $enrollee->whereIn('id', $enrolleeIds);
            })
            ->whereHas(
                'user.patientInfo',
                function ($patient) {
                    $patient->where('ccm_status', Patient::UNREACHABLE);
                }
            )
            ->whereNotNull('user_id')
            ->chunk(
                50,
                function ($enrollees) {
                    Enrollee::whereIn(
                        'id',
                        $enrollees->pluck('id')
                            ->all()
                    )
                        ->update(
                            [
                                'status' => Enrollee::QUEUE_AUTO_ENROLLMENT,
                            ]
                        );

                    foreach ($enrollees as $enrollee) {
                        SendReminder::dispatch(new User($enrollee->user->toArray()));
                        $this->info("SendReminder JOB queued for Enrollee $enrollee->id");
                    }
                }
            );
    }
}
