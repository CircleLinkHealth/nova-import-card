<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Jobs;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\Nurseinvoices\GenerateInvoiceDownload;

class CreateDownlableInvoices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var string
     */
    private $downloadFormat;
    /**
     * @var NurseInvoice
     */
    private $invoices;

    /**
     * Create a new job instance.
     *
     * @param NurseInvoice $invoices
     */
    public function __construct(Collection $invoices, string $downloadFormat)
    {
        $this->invoices       = $invoices;
        $this->downloadFormat = $downloadFormat;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (NurseInvoice::PDF_DOWNLOAD_FORMAT === $this->downloadFormat) {
            $invoicesPdf = (new GenerateInvoiceDownload($this->invoices, $this->downloadFormat))->generateInvoicePdf();
        }
    }
}
