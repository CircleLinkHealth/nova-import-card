<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;
use CircleLinkHealth\NurseInvoices\Jobs\GenerateNurseMonthlyInvoiceCsv;
use CircleLinkHealth\NurseInvoices\Notifications\ResolveDisputeReminder;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\NurseInvoices\Traits\TakesMonthAndUsersAsInputArguments;
use CircleLinkHealth\SharedModels\Entities\NurseInvoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendResolveInvoiceDisputeReminder extends Command
{
    use DryRunnable;
    use TakesMonthAndUsersAsInputArguments;

    const NURSE_DISPUTES_MANAGER = 'nurse_invoice_dispute_manager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve Dispute Reminder';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'nurseinvoice:resolveDispute';

    public function emailsToSendNotif()
    {
        $getEmails = AppConfig::pull(self::NURSE_DISPUTES_MANAGER, []);
        foreach ($getEmails as $email) {
            $this->info('Will email'.$email);
        }

        return $getEmails;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $month = $this->month();

        $this->info('Run command for month: '.$month->toDateString());

        $disputesCount = $this->unresolvedDisputesCount($month);

        if (0 !== $disputesCount && isProductionEnv()) {
            $this->sendNotificationsTo($disputesCount);
        }

        GenerateNurseMonthlyInvoiceCsv::dispatch($month)
            ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE));

        $this->info('Command finished');
    }

    public function sendNotificationsTo($disputesCount)
    {
        $sendNotifAt       = Carbon::now()->addHours(7);
        $emailsToSendNotif = $this->emailsToSendNotif();

        foreach ($emailsToSendNotif as $email) {
            Notification::route('mail', $email)
                ->notify((new ResolveDisputeReminder($disputesCount))->delay($sendNotifAt));
        }
    }

    /**
     * Returns whether the command should be skipped, ie. not run.
     *
     * @return bool
     */
    public static function shouldSkip()
    {
        $disputeSubmissionDeadline = NurseInvoiceDisputeDeadline::for(Carbon::now()->subMonth());

        $disputeResolutionDeadline = $disputeSubmissionDeadline->copy()->addDays(2);

        $today = Carbon::now();
        //This is when we dispute submissions and resolutions begin.
        $disputesCanExist = $disputeResolutionDeadline->copy()->startOfMonth();
        //The month before $disputesCanExist is the month for which we are generating invoices for.
        $invoiceMonth                         = $disputesCanExist->copy()->subMonth()->startOfMonth();
        $invoicesNotSentToAccountantSentQuery = \CircleLinkHealth\SharedModels\Entities\NurseInvoice::where('month_year', $invoiceMonth)->whereNull('sent_to_accountant_at');
        if ( ! $invoicesNotSentToAccountantSentQuery->exists()) {
            return true;
        }

        if ($today->lte($disputesCanExist)) {
            return true;
        }

        if ($today->lte($disputeSubmissionDeadline)) {
            return true;
        }

        $disputesExist = \CircleLinkHealth\SharedModels\Entities\Dispute::whereIn('disputable_id', \CircleLinkHealth\SharedModels\Entities\NurseInvoice::where('month_year', $invoiceMonth)->pluck('id'))->where('is_resolved', false)->exists();

        if ( ! $disputesExist) {
            return false;
        }

        //not sure this is still necessary
        if ($today->lte($disputeResolutionDeadline)) {
            return true;
        }
    }

    public function unresolvedDisputesCount($month)
    {
        return NurseInvoice::where('month_year', $month)
            ->whereHas('disputes', function ($q) {
                $q->whereNull('resolved_at');
            })
            ->count();
    }
}
