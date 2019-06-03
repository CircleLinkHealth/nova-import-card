<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

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

class GenerateNurseMonthlyInvoiceCsv implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        //@todo: accountant's mail.

        $accountant = User::find(9521);

        $accountant->notify(new SendMonthlyInvoicesToAccountant($this->date, $csvInvoices));
    }
}
