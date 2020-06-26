<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
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
    /**
     * @var string
     */
    private $downloadFormat;

    private $month;

    /**
     * @var int
     */
    private $practiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $practiceId, string $downloadFormat, Carbon $month)
    {
        $this->practiceId     = $practiceId;
        $this->downloadFormat = $downloadFormat;
        $this->month          = $month;
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

        $x = NurseInvoice::with([
            'nurseInfo' => function ($nurseInfo) use ($startDate, $endDate) {
                $nurseInfo->with(
                    [
                        'user.pageTimersAsProvider' => function ($pageTimer) use ($startDate, $endDate) {
                            $pageTimer->whereBetween('start_time', [$startDate, $endDate]);
                        },
                    ]
                );
            },
            'nurseInfo.user',
            //            Need nurses that are currently active or used to be for selected month
        ])->whereHas(
            'nurseInfo',
            function ($info) {
                $info->where('status', 'active')
                    ->when(isProductionEnv(), function ($info) {
                        $info->where('is_demo', false);
                    });
            }
        )
            ->whereHas('nurseInfo.user', function ($user) {
                $user->where('program_id', $this->practiceId);
            })
            ->orWhereHas('nurseInfo.user.pageTimersAsProvider', function ($pageTimersAsProvider) use ($startDate, $endDate) {
                $pageTimersAsProvider->whereBetween('start_time', [$startDate, $endDate]);
            })
//             The above should be fixed.
                // 1. Where active , 2. Where $practice, 3. Or where PageTimer and $practice
            ->chunk(20, function ($invoices) use ($startDate, $endDate) {
                foreach ($invoices as $invoice) {
                    //                PDF / CSV the invoice...
                }
            });

        $x = 1;
    }
}
