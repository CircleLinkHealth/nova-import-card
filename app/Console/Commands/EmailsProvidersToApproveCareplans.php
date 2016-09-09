<?php

namespace App\Console\Commands;

use App\CLH\Contracts\Repositories\UserRepository;
use App\PatientCarePlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailsProvidersToApproveCareplans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailapprovalreminder:providers';

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
     * @return void
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
        $providers = $this->users->findByRole('provider');

        $emailsSent = $providers->map(function ($user) {
            $recipients = [
                $user->user_email
            ];

            $numberOfCareplans = PatientCarePlan::getNumberOfCareplansPendingApproval($user);

            if ($numberOfCareplans < 1) return false;

            $data = [
                'numberOfCareplans' => $numberOfCareplans,
                'drName' => $user->fullName,
            ];

            $view = 'emails.careplansPendingApproval';
            $subject = "{$numberOfCareplans} CircleLink Care Plans for your Approval!";


            Mail::send($view, $data, function ($message) use ($recipients, $subject) {
                $message->from('notifications@careplanmanager.com', 'CircleLink Health')
                    ->to($recipients)
                    ->subject($subject);
            });

            Slack::to('#background-tasks')
                ->send("Sent pending approvals email to {$user->fullName}.");
        });
    }
}
