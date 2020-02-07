<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Core\Entities\AppConfig;
use App\Exports\NurseInvoiceCsv;
use App\Notifications\SendMonthlyInvoicesToAccountant;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class GenerateNurseMonthlyInvoiceCsv implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const RECEIVES_NURSE_INVOICES_CSV = 'receives_nurse_invoices_csv';

    /**
     * @var Carbon
     */
    public $date;

    /**
     * Create a new job instance.
     *
     * @param Carbon $month
     */
    public function __construct(Carbon $month)
    {
        $this->date = $month;
        $this->date->startOfMonth();
    }

    public function csvReceivers()
    {
        $getEmails = [];
        AppConfig::where('config_key', '=', self::RECEIVES_NURSE_INVOICES_CSV)
            ->select('config_value')->chunk(20, function ($emails) use (&$getEmails) {
                foreach ($emails as $email) {
                    $getEmails[] = $email->config_value;
                }
            });

        return $getEmails;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $report = (new NurseInvoiceCsv($this->date));

        $csvInvoices = $report->storeAndAttachMediaTo(SaasAccount::whereSlug('circlelink-health')->firstOrFail());

        $this->sendCsvInvoicesTo($csvInvoices);

        $this->markMonthInvoicesAsSent($report);
    }

    public function sendCsvInvoicesTo($csvInvoices)
    {
        $sendNotifAt  = Carbon::now()->addHours(7);
        $csvReceivers = $this->csvReceivers();
        foreach ($csvReceivers as $csvReceiver) {
            Notification::route('mail', $csvReceiver)
                ->notify((new SendMonthlyInvoicesToAccountant($this->date, $csvInvoices))->delay($sendNotifAt));
        }
    }

    private function markMonthInvoicesAsSent(NurseInvoiceCsv $report)
    {
        return $report->invoicesQuery()->update(['sent_to_accountant_at' => Carbon::now()]);
    }
}
