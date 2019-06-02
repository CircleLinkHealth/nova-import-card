<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Exports\NurseInvoiceCsv;
use App\Notifications\SendMonthlyInvoicesToAccountant;
use Carbon\Carbon;
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

    public $date;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $startOfMonth)
    {
        $this->date = $startOfMonth;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $media = (new NurseInvoiceCsv($this->date))->collection();

        //return (new NurseInvoiceCsv($this->date))->download('invoices', \Maatwebsite\Excel\Excel::CSV);

        $user = User::find(9521);

        $user->notify(new SendMonthlyInvoicesToAccountant($this->date, $media));
    }
}
