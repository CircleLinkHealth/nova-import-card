<?php

namespace App\Console\Commands;

use App\CLH\Contracts\Repositories\UserRepository;
use App\Models\EmailSettings;
use App\PatientCarePlan;
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
     * An instance of UserRepository.
     *
     * @var UserRepository
     */
    protected $users;

    /**
     * Create a new command instance.
     *
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        parent::__construct();

        $this->users = $users;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pretend = $this->option('pretend');

        $providers = $this->users->findByRole('provider');

        $bar = $this->output->createProgressBar(count($providers));

        $emailsSent = $providers->map(function ($user) use
        (
            $bar,
            $pretend
        ) {

            //Middletown
            if ($user->program_id == 23) {
                return false;
            }

            //Miller
            if ($user->program_id == 10) {
                return false;
            }
            //Icli
            if ($user->program_id == 19) {
                return false;
            }
            //Purser
            if ($user->program_id == 22) {
                return false;
            }


            $recipients = [
                $user->email,
                //            'raph@circlelinkhealth.com',
                //            'mantoniou@circlelinkhealth.com',
            ];

            $numberOfCareplans = PatientCarePlan::getNumberOfCareplansPendingApproval($user);

            if ($numberOfCareplans < 1) {
                return false;
            }

            $data = [
                'numberOfCareplans' => $numberOfCareplans,
                'drName'            => $user->fullName,
            ];

            $view = 'emails.careplansPendingApproval';
            $subject = "{$numberOfCareplans} CircleLink Care Plan(s) for your Approval!";

            $settings = $user->emailSettings()->firstOrNew([]);

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
                if ($send) {
                    Mail::send($view, $data, function ($message) use
                    (
                        $recipients,
                        $subject
                    ) {
                        $message->from('notifications@careplanmanager.com', 'CircleLink Health')
                            ->to($recipients)
                            ->subject($subject);
                    });
                }

                Slack::to('#background-tasks')
                    ->send("Sent pending approvals email to {$user->fullName}.");
            }

            $bar->advance();

            return [
                'provider'         => $user->fullName,
                'pendingApprovals' => $numberOfCareplans,
            ];
        });

        $emailsSent = array_filter($emailsSent->all());

        $bar->finish();

        $this->table([
            'provider',
            'pendingApprovals',
        ], $emailsSent);

        $count = count($emailsSent);

        $this->info("Sent $count emails.");
    }
}
