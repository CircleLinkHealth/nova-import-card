<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\Nurseinvoices\GenerateInvoicesExport;
use CircleLinkHealth\NurseInvoices\Notifications\NurseInvoicesDownloaded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
    private $downloadFormat;
    /**
     * @var Carbon
     */
    private $month;

    /**
     * Create a new job instance.
     */
    public function __construct(string $downloadFormat, Carbon $month, User $auth)
    {
        $this->downloadFormat = $downloadFormat;
        $this->month          = $month;
        $this->auth           = $auth;
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
        $invoices  = collect();

        User::withDownloadableInvoices($startDate, $endDate)
            ->select('id')
            ->chunk(100, function ($users) use ($startDate, $endDate, &$invoices) {
                $users->each(function ($user) use ($invoices) {
                    $invoices->push($user->nurseInfo->invoices->first());
                });
            });

        if ($invoices->isEmpty()) {
            Log::warning("Invoices to download for {$startDate} not found");
        }

        $invoicesMediaIds = $this->generateInvoicesMedia(collect([$invoices]), $startDate);
        $this->auth->notify(new NurseInvoicesDownloaded($invoicesMediaIds, $startDate, $this->downloadFormat));
    }

    public function invoicesAreChunked(Collection $invoices): bool
    {
        return $invoices->count() > 1;
    }

    /**
     * @return array
     */
    private function generateInvoicesMedia(Collection $invoices, Carbon $startDate)
    {
        if (0 == strcasecmp(NurseInvoice::PDF_DOWNLOAD_FORMAT, $this->downloadFormat)) {
            $invoiceDocument = (new GenerateInvoicesExport($invoices, $this->downloadFormat, $startDate))->generateInvoicePdf();

            return collect($invoiceDocument)->pluck('mediaIds')->flatten()->toArray();
        }

        if (0 == strcasecmp(NurseInvoice::CSV_DOWNLOAD_FORMAT, $this->downloadFormat)) {
            $invoiceDocument = (new GenerateInvoicesExport($invoices, $this->downloadFormat, $startDate))->generateCsvWithInvoices();

            return collect($invoiceDocument)->pluck('id')->toArray();
        }

        return [];
    }
}
