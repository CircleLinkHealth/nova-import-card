<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Nurseinvoices\Services;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Support\Collection;
use Modules\Nurseinvoices\GenerateInvoiceDownload;

class DownloadNurseInvoiceService
{
    /**
     * @param $date
     *
     * @return array|\Spatie\MediaLibrary\Models\Media
     */
    public function invoicesDownloadLink(Collection $invoices, string $downloadFormat, $date)
    {
        if (NurseInvoice::PDF_DOWNLOAD_FORMAT === $downloadFormat) {
            return (new GenerateInvoiceDownload($invoices, $date))->generateInvoicePdf();
        }

        if (NurseInvoice::CSV_DOWNLOAD_FORMAT === $downloadFormat) {
            return (new GenerateInvoiceDownload($invoices, $date))->generateInvoiceCsv();
        }
    }
}
