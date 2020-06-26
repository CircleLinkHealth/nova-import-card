<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NurseInvoiceDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $month;

    /**
     * @var NurseInvoice
     */
    private $nurseInvoice;

    /**
     * Create a new job instance.
     */
    public function __construct(NurseInvoice $nurseInvoice)
    {
        $this->nurseInvoice = $nurseInvoice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $startDate = $this->month->copy()->startOfMonth()->toDateString();
        $endDate   = $this->month->copy()->endOfMonth()->toDateString();

        NurseInvoice::with([
            'nurseInfo' => function ($nurseInfo) use ($startDate, $endDate) {
                $nurseInfo->with(
                    [
                        'user.pageTimersAsProvider' => function ($pageTimer) use ($startDate, $endDate) {
                            $pageTimer->whereBetween('start_time', [$startDate, $endDate]);
                        },
                    ]
                );
            },
            //            Need nurses that are currently active or used to be for selected month
        ])->whereHas(
            'nurseInfo',
            function ($info) {
                $info->where('status', 'active')
                    ->when(isProductionEnv(), function ($info) {
                        $info->where('is_demo', false);
                    });
            }
        )->orWhereHas('nurseInfo.user.pageTimersAsProvider', function ($user) use ($startDate, $endDate) {
            $user->whereBetween('start_time', [$startDate, $endDate]);
        })
            ->chunk(20, function ($invoices) use ($startDate, $endDate) {
                foreach ($invoices as $invoice) {
//                Download the invoice...
                }
            });
    }
}
