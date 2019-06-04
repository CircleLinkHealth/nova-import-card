<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\AppConfig;
use App\Jobs\GenerateNurseMonthlyInvoiceCsv;
use App\Notifications\ResolveDisputeReminder;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\NurseInvoices\Traits\TakesMonthAndUsersAsInputArguments;
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
    protected $signature = 'nurseinvoice:resolveDispute';

    public function emailsToSendNotif()
    {
        $getEmails = [];
        AppConfig::where('config_key', '=', self::NURSE_DISPUTES_MANAGER)
            ->select('config_value')->chunk(20, function ($emails) use (&$getEmails) {
                foreach ($emails as $email) {
                    $getEmails[] = $email->config_value;
                    $this->info('Will email'.$email->config_value);
                }
            });

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
            ->onQueue('high');

        $this->info('Command finished');
    }

    public function sendNotificationsTo($disputesCount)
    {
        $sendNotifAt       = Carbon::now()->addHour(7);
        $emailsToSendNotif = $this->emailsToSendNotif();

        foreach ($emailsToSendNotif as $email) {
            Notification::route('mail', $email)
                ->notify((new ResolveDisputeReminder($disputesCount))->delay($sendNotifAt));
        }
    }

    public function unresolvedDisputesCount($month)
    {
        return NurseInvoice::where('month_year', $month)
            ->whereHas('dispute', function ($q) use ($month) {
                $q->whereNull('resolved_at');
            })
            ->count();
    }
}
