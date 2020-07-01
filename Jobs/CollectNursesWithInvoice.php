<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Notifications\NurseInvoicesDownloaded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Nurseinvoices\Services\DownloadNurseInvoiceService;

class CollectNursesWithInvoice implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var User
     */
    private $auth;
    /**
     * @var string
     */
    private $downloadFormat;

    private $month;

    /**
     * @var int
     */
    private $practiceIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $practiceIds, string $downloadFormat, Carbon $month, User $auth)
    {
        $this->practiceIds    = $practiceIds;
        $this->downloadFormat = $downloadFormat;
        $this->month          = $month;
        $this->auth           = $auth;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DownloadNurseInvoiceService $downloadNurseInvoiceService)
    {
        $startDate = $this->month->copy()->startOfMonth();
        $endDate   = $this->month->copy()->endOfMonth();

        $invoices = [];

        User::with([
            'nurseInfo' => function ($nurseInfo) use ($startDate, $endDate) {
                $nurseInfo->with(
                    [
                        'invoices' => function ($invoice) use ($startDate) {
                            $invoice->where('month_year', $startDate);
                        },
                    ]
                );
            },
            'pageTimersAsProvider' => function ($pageTimer) use ($startDate, $endDate) {
                $pageTimer->whereBetween('start_time', [$startDate, $endDate]);
            },
        ])
            ->whereHas('nurseInfo.invoices', function ($invoice) use ($startDate, $endDate) {
                $invoice->where('month_year', $startDate);
            })
            //            Need nurses that are currently active or used to be for selected month
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereHas(
                    'nurseInfo',
                    function ($info) {
                        $info->where('status', 'active')->when(
                            isProductionEnv(),
                            function ($info) {
                                $info->where('is_demo', false);
                            }
                        );
                    }
                )
                    ->orWhereHas('pageTimersAsProvider', function ($pageTimersAsProvider) use ($startDate, $endDate) {
                        $pageTimersAsProvider->whereBetween('start_time', [$startDate, $endDate]);
                    });
            })
            ->whereIn('program_id', $this->practiceIds)
            ->select('id', 'program_id')
            ->chunk(20, function ($users) use ($startDate, $endDate, &$invoices) {
                foreach ($users as $user) {
                    $invoices[] = $user->nurseInfo->invoices;
                }
            });

        $invoicesFlatten = collect($invoices)->flatten();

        if (empty($invoicesFlatten)) {
            Log::warning('Invoices to download not found');

            return;
        }

        $invoiceDocument = $downloadNurseInvoiceService->invoicesDownloadLink($invoicesFlatten, $this->downloadFormat, $startDate);

        $this->auth->notify(new NurseInvoicesDownloaded([$invoiceDocument->id], $startDate));
        //        Notify user - admin. NurseInvoicesDownloaded
    }
}
