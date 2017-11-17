<?php

namespace App\Console\Commands;

use App\CarePlan;
use App\Mail\CarePlanApprovalReminder;
use App\Models\EmailSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maknz\Slack\Facades\Slack;

class EmailsProvidersToApproveCareplans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailapprovalreminder:providers {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a reminder email to all Providers telling them how many Careplans are awaiting approval.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pretend = $this->option('pretend');

        $providers = User::ofType('provider')->get();

        $bar = $this->output->createProgressBar(count($providers));

        $emailsSent = $providers->map(function ($providerUser) use (
            $bar,
            $pretend
        ) {
            if (!$this->shouldSend($providerUser)) {
                return false;
            }

            $recipients = $this->recipients($providerUser);

            if ($recipients->isEmpty()) {
                return false;
            }

            $numberOfCareplans = CarePlan::getNumberOfCareplansPendingApproval($providerUser);

            if ($numberOfCareplans < 1) {
                return false;
            }

            foreach ($recipients as $recipient) {
                $this->sendEmail($recipient, $numberOfCareplans, $providerUser, $pretend);
                $bar->advance();
            }

            return [
                'practice'         => $providerUser->primaryPractice->display_name,
                'receivers'        => implode(', ', $recipients->all()),
                'pendingApprovals' => $numberOfCareplans,
            ];
        });

        $emailsSent = array_filter($emailsSent->all());

        $bar->finish();

        $this->table([
            'practice',
            'receivers',
            'pendingApprovals',
        ], $emailsSent);

        $count = count($emailsSent);

        $this->info("$count emails.");
    }

    public function shouldSend(User $providerUser)
    {
        //Middletown
        if ($providerUser->program_id == 23) {
            return false;
        }

        //Miller
        if ($providerUser->program_id == 10) {
            return false;
        }
        //Icli
        if ($providerUser->program_id == 19) {
            return false;
        }
        //Purser
        if ($providerUser->program_id == 22) {
            return false;
        }

        if (!$providerUser->primaryPractice) {
            return false;
        }

        if (!$providerUser->primaryPractice->cpmSettings()->email_careplan_approval_reminders) {
            return false;
        }

        if ($providerUser->primaryPractice->cpmSettings()->auto_approve_careplans) {
            return false;
        }

        return true;
    }

    public function recipients(User $providerUser)
    {
        $recipients = collect();

        if ($providerUser->forwardAlertsTo->isEmpty()) {
            $recipients->push($providerUser);
        } else {
            foreach ($providerUser->forwardAlertsTo as $forwardee) {
                if ($forwardee->pivot->name == User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER) {
                    $recipients->push($providerUser);
                    $recipients->push($forwardee);
                }

                if ($forwardee->pivot->name == User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER) {
                    $recipients->push($forwardee);
                }
            }
        }

        return $recipients;
    }

    public function sendEmail(User $recipient, $numberOfCareplans, User $providerUser, bool $pretend)
    {
        $settings = $providerUser->emailSettings()->firstOrNew([]);

        $send = $settings->frequency == EmailSettings::DAILY
            ? true
            : ($settings->frequency == EmailSettings::WEEKLY) && Carbon::today()->dayOfWeek == 1
                ? true
                : ($settings->frequency == EmailSettings::MWF) &&
                (Carbon::today()->dayOfWeek == 1
                    || Carbon::today()->dayOfWeek == 3
                    || Carbon::today()->dayOfWeek == 5)
                    ? true
                    : false;

        if (!$send) {
            return false;
        }

        if (!$pretend) {
            if ($send && $recipient->email) {
                Mail::send(new CarePlanApprovalReminder($recipient, $numberOfCareplans));
            }
        }
    }
}
