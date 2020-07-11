<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\CarePlanApprovalRequestsReceivers;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Console\Command;

class SendCarePlanApprovalReminders extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a reminder email to all Providers telling them how many Careplans are awaiting approval.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendapprovalreminder:providers {--pretend}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pretend = $this->option('pretend');

        $providersCount = User::ofType('provider')
            ->count();

        $bar = $this->output->createProgressBar($providersCount);

        //Recipients that do NOT have emr_direct_address, and practice reminder notifications are DIRECT mail: 1, Email: 0
        $recipientsWithNoDMAddresses = collect();

        $emailsSent = [];

        User::ofType('provider')
            ->with('forwardAlertsTo')
            ->chunk(50, function ($providers) use (
                $bar,
                $pretend,
                &$recipientsWithNoDMAddresses,
                &$emailsSent
            ) {
                foreach ($providers as $providerUser) {
                    if ( ! $this->shouldSend($providerUser)) {
                        continue;
                    }

                    $recipients = $this->recipients($providerUser)
                        ->unique('id')
                        ->values();

                    if ($recipients->isEmpty()) {
                        continue;
                    }

                    $numberOfCareplans = CarePlan::getNumberOfCareplansPendingApproval($providerUser);

                    if ($numberOfCareplans < 1) {
                        continue;
                    }

                    foreach ($recipients as $recipient) {
                        if ( ! $recipient->practiceSettings()->email_careplan_approval_reminders && $recipient->practiceSettings()->dm_careplan_approval_reminders && ! $recipient->emr_direct_address) {
                            $recipientsWithNoDMAddresses->push("{$recipient->getFullName()}, number of careplans pending approval: {$numberOfCareplans}");
                            $bar->advance();
                        } else {
                            $this->sendEmail($recipient, $numberOfCareplans, $pretend);
                            $bar->advance();
                        }
                    }

                    $emailsSent[] = [
                        'practice'         => $providerUser->primaryPractice->display_name,
                        'receivers'        => $recipients->implode('display_name', ', '),
                        'pendingApprovals' => $numberOfCareplans,
                    ];
                }
            });

        if (isProductionEnv() && $recipientsWithNoDMAddresses->isNotEmpty()) {
            sendSlackMessage(
                '#customersuccess',
                "We were not able to send Care Plan Approval Notifications via DIRECT to these providers because no DIRECT addresses were found.\n{$recipientsWithNoDMAddresses->implode(",\n")}"
            );
        }
        $bar->finish();

        $this->table([
            'practice',
            'receivers',
            'pendingApprovals',
        ], $emailsSent);

        $count = count($emailsSent);

        $this->info("${count} emails.");
    }

    public function recipients(User $providerUser)
    {
        return CarePlanApprovalRequestsReceivers::forProvider($providerUser);
    }

    public function sendEmail(User $recipient, $numberOfCareplans, bool $pretend)
    {
        if ( ! $pretend) {
            if ($recipient->email) {
                $recipient->sendCarePlanApprovalReminder($numberOfCareplans);
            }
        }
    }

    public function shouldSend(User $providerUser)
    {
        //Middletown
        if (23 == $providerUser->program_id) {
            return false;
        }

        //Miller
        if (10 == $providerUser->program_id) {
            return false;
        }
        //Icli
        if (19 == $providerUser->program_id) {
            return false;
        }
        //Purser
        if (22 == $providerUser->program_id) {
            return false;
        }

        if ( ! $providerUser->primaryPractice) {
            return false;
        }

        if ( ! $providerUser->practiceSettings()->email_careplan_approval_reminders && ! $providerUser->practiceSettings()->dm_careplan_approval_reminders) {
            return false;
        }

        if ($providerUser->primaryPractice->cpmSettings()->auto_approve_careplans) {
            return false;
        }

        return true;
    }
}
