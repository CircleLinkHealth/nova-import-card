<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\CarePlan;
use App\User;
use Illuminate\Console\Command;

class EmailsProvidersToApproveCareplans extends Command
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
    protected $signature = 'emailapprovalreminder:providers {--pretend}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pretend = $this->option('pretend');

        $providers = User::ofType('provider')
                         ->with('forwardAlertsTo')
                         ->get();

        $bar = $this->output->createProgressBar(count($providers));

        //Recipients that do NOT have emr_direct_address, and practice reminder notifications are DIRECT mail: 1, Email: 0
        $recipientsWithNoDMAddresses = collect([]);

        $emailsSent = $providers->map(function ($providerUser) use (
            $bar,
            $pretend,
            $recipientsWithNoDMAddresses
        ) {
            if ( ! $this->shouldSend($providerUser)) {
                return false;
            }

            $recipients = $this->recipients($providerUser)
                               ->unique('id')
                               ->values();

            if ($recipients->isEmpty()) {
                return false;
            }

            $numberOfCareplans = CarePlan::getNumberOfCareplansPendingApproval($providerUser);

            if ($numberOfCareplans < 1) {
                return false;
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

            return [
                'practice'         => $providerUser->primaryPractice->display_name,
                'receivers'        => $recipients->implode('display_name', ', '),
                'pendingApprovals' => $numberOfCareplans,
            ];
        });

        if ( ! app()->environment(['local', 'staging']) && $recipientsWithNoDMAddresses->isNotEmpty()) {
            sendSlackMessage(
                '#implementations',
                "We were not able to send Care Plan Approval Notifications via DIRECT to these providers because no DIRECT addresses were found.\n{$recipientsWithNoDMAddresses->implode(",\n")}"
            );
        }
        $emailsSent = array_filter($emailsSent->all());

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
        $recipients = collect();

        if ($providerUser->forwardAlertsTo->isEmpty()) {
            $recipients->push($providerUser);
        } else {
            foreach ($providerUser->forwardAlertsTo as $forwardee) {
                if (User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER == $forwardee->pivot->name) {
                    $recipients->push($providerUser);
                    $recipients->push($forwardee);
                }

                if (User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER == $forwardee->pivot->name) {
                    $recipients->push($forwardee);
                }
            }
        }

        return $recipients;
    }

    public function sendEmail(User $recipient, $numberOfCareplans, bool $pretend)
    {
        if ( ! $pretend) {
            if ($recipient->email) {
                $recipient->sendCarePlanApprovalReminderEmail($numberOfCareplans);
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
