<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\AppConfig;
use App\Exports\NurseInvoiceCsv;
use App\Notifications\SendMonthlyInvoicesToAccountant;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
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
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $csvInvoices = (new NurseInvoiceCsv($this->date))
            ->storeAndAttachMediaTo(SaasAccount::whereSlug('circlelink-health')->firstOrFail());

        $usersToSendCsv = $this->usersToSendCsv();

        Notification::send($usersToSendCsv, new SendMonthlyInvoicesToAccountant($this->date, $csvInvoices));
    }

    public function usersToSendCsv()
    {//i want to export this and use it in "SendResolveInvoiceDisputeReminder" also by changing the "config_key" to search
        $getEmails = [];
        AppConfig::where('config_key', '=', self::RECEIVES_NURSE_INVOICES_CSV)
            ->select('config_value')->chunk(20, function ($emails) use (&$getEmails) {
                foreach ($emails as $email) {
                    $getEmails[] = $email->config_value;
                }
            });
        //will this query slow things down?
        return User::whereIn('email', $getEmails)->get();
    }
}
