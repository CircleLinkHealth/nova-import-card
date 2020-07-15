<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Notifications\NurseInvoicesDownloaded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Nurseinvoices\GenerateInvoicesExport;

class ExportAndDispatchInvoices implements ShouldQueue
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
    private $downloadFormats;

    private $month;

    /**
     * @var int
     */
    private $practiceIds;

    /**
     * Create a new job instance.
     *
     * @param string $downloadFormats
     */
    public function __construct(array $practiceIds, array $downloadFormats, Carbon $month, User $auth)
    {
        $this->practiceIds     = $practiceIds;
        $this->downloadFormats = $downloadFormats;
        $this->month           = $month;
        $this->auth            = $auth;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $startDate = $this->month->copy()->startOfMonth();
        $endDate   = $this->month->copy()->endOfMonth();

        $invoices = collect();
        User::withDownloadableInvoices($startDate, $endDate)
            ->whereIn('program_id', $this->practiceIds)
            ->select('id', 'program_id')
            ->chunk(20, function ($users) use ($startDate, $endDate, &$invoices) {
                $invoices[] = $users->transform(function ($user) {
                    return [
                        'data'        => $user->nurseInfo->invoices,
                        'practice_id' => $user->primaryPractice->id,
                    ];
                });
            });

        $invoicesPerPractice = $invoices->flatten(1)->groupBy('practice_id');

        if (empty($invoicesPerPractice)) {
            //            Code execution will continue. It will dispatch a Notification with info that nothing was generated.
            Log::warning("Invoices to download for {$startDate} not found");
        }

        $this->generateInvoicesFormatAndDispatch($invoicesPerPractice, $startDate);
    }

    private function generateInvoicesFormatAndDispatch(Collection $invoicesPerPractice, Carbon $startDate)
    {
        foreach ($this->downloadFormats as $downloadFormat) {
            $mediaIds = [];

            if (NurseInvoice::PDF_DOWNLOAD_FORMAT === $downloadFormat) {
                $invoiceDocument = (new GenerateInvoicesExport($invoicesPerPractice, $downloadFormat, $startDate))->generateInvoicePdf();
                $mediaIds        = collect($invoiceDocument)->pluck('mediaIds')->flatten()->toArray();
            }

            if (NurseInvoice::CSV_DOWNLOAD_FORMAT === $downloadFormat) {
                $invoiceDocument = (new GenerateInvoicesExport($invoicesPerPractice, $downloadFormat, $startDate))->generateInvoiceCsv();
                $mediaIds        = collect($invoiceDocument)->pluck('id')->toArray();
            }

            $this->auth->notify(new NurseInvoicesDownloaded($mediaIds, $startDate, $downloadFormat));
        }
    }
}
