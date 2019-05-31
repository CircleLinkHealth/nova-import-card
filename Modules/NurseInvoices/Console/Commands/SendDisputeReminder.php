<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Notifications\InvoiceReminder;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\NurseInvoices\Traits\TakesMonthAndUsersAsInputArguments;
use Illuminate\Console\Command;

class SendDisputeReminder extends Command
{
    use DryRunnable;
    use TakesMonthAndUsersAsInputArguments;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the LAN (Last Approval Notification) to the nurses for invoices of the given month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseinvoices:lan';

    /**
     * @var \Carbon|null
     */
    private $deadlineInstance;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = 0;

        $this->invoices()
            ->chunk(
                20,
                function ($invoices) use ($count) {
                    foreach ($invoices as $invoice) {
                        $this->sendInvoice($invoice);
                        ++$count;
                    }
                }
             );

        $this->info("Command finished! Sent $count Notifications.");
    }

    public function invoices()
    {
        return NurseInvoice::with('nurse.user')
            ->has('nurse.user')
            ->when(
                ! empty($userIds),
                function ($q) {
                    $q->whereHas('nurse.user', function ($q) {
                        $q->whereIn('id', $this->usersIds());
                    });
                }
                           )
            ->where('month_year', $this->month())
            ->undisputed()
            ->notApproved();
    }

    /**
     * Returns a new instance of the deadline.
     *
     * @return \Carbon\Carbon
     */
    private function deadline()
    {
        if ( ! $this->deadlineInstance) {
            $this->deadlineInstance = NurseInvoiceDisputeDeadline::forInvoice($this->month());
        }

        return $this->deadlineInstance->copy();
    }

    private function sendInvoice($invoice)
    {
        $this->warn("Sending notification to {$invoice->nurse->user->getFullName()}");

        $invoice->nurse->user->notify(
            new InvoiceReminder($this->deadline()->setTimezone($invoice->nurse->user->timezone ?? 'America/New_York'), $this->month())
        );

        $this->info("Sent notification to {$invoice->nurse->user->getFullName()}");
    }
}
