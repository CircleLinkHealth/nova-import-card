<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Notifications\InvoiceReviewInitialReminder;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\NurseInvoices\Traits\TakesMonthAndUsersAsInputArguments;
use Illuminate\Console\Command;

class SendMonthlyNurseInvoiceFAN extends Command
{
    use DryRunnable;
    use TakesMonthAndUsersAsInputArguments;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the FAN (First Approval Notification) to the nurses for invoices of the given month.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nurseinvoices:fan';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = 0;
        $this->users()->chunk(50, function ($users) use (&$count) {
            foreach ($users as $user) {
                $this->notifyNurse($user);
                ++$count;
            }
        });

        $this->info('Command finished!');
        $this->info("Sent $count Notifications for {$this->month()->format('F Y')}.");
    }

    /**
     * Send a notification to the nurse.
     */
    public function notifyNurse(User $user)
    {
        $name = $user->getFullName();
        $this->warn("Notifying $name.");

        if ( ! $this->isDryRun()) {
            $user->notify(new InvoiceReviewInitialReminder($user->nurseInfo->invoices->first()));
        }

        $this->info("Notified $name.");
    }

    private function users()
    {
        return User::ofType('care-center')->whereHas('nurseInfo.invoices', function ($q) {
            $q->where('month_year', $this->month()->startOfMonth())
                ->doesntHave('notifications');
        })->with(['nurseInfo.invoices' => function ($q) {
            $q->where('month_year', $this->month()->startOfMonth());
        }])->when( ! empty($userIds), function ($q) {
            $q->whereIn('id', $this->usersIds());
        });
    }
}
