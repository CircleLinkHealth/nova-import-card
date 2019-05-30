<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Exports\NurseInvoiceCsv;
use Carbon\Carbon;
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
        new NurseInvoiceCsv($this->date);
    }
}
