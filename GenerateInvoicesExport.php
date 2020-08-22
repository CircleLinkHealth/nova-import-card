<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\NurseInvoices\Exports\InvoicesExportFormat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GenerateInvoicesExport
{
    /**
     * @var
     */
    private $date;
    private $downloadFormat;

    /**
     * @var Collection
     */
    private $invoices;

    /**
     * GenerateInvoicesExport constructor.
     */
    public function __construct(Collection $invoices, string $downloadFormat, \Carbon\Carbon $date)
    {
        $this->invoices       = $invoices;
        $this->date           = $date;
        $this->downloadFormat = $downloadFormat;
    }

    public function generateCsvWithInvoices()
    {
        $csvInvoices  = [];
        $month        = Carbon::parse($this->date)->format('M-Y');
        $downloadName = "$month.csv";

        $model = \Cache::remember("cached_saas_account_$downloadName", 2, function () {
            return SaasAccount::whereSlug('circlelink-health')->firstOrFail();
        });

        foreach ($this->invoices as $invoicesData) {
            $csvInvoices[] = (new FromArray(
                "$downloadName",
                (new InvoicesExportFormat(
                    $invoicesData
                ))->toCsvArray(),
                [
                ]
            ))->storeAndAttachMediaTo($model, $downloadName);
        }

        return $csvInvoices;
    }

    /**
     * @return array
     */
    public function generateInvoicePdf()
    {
        $data = [];
        foreach ($this->invoices as $invoicesData) {
            $invoicesForMonth = Carbon::parse($this->date)->format('M-Y');
            $downloadName     = $invoicesForMonth;
            $pdfInvoices      = $this->makeInvoicesPdf($downloadName, $invoicesData);

            $data[] = [
                'invoice_url' => $pdfInvoices->getUrl(),
                'mediaIds'    => [$pdfInvoices->id],
            ];
        }

        return $data;
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    private function makeInvoicesPdf(string $downloadName, Collection $invoices)
    {
        $pdfService = app(PdfService::class);
        $path       = storage_path("download/$downloadName.pdf");

        $pdfInvoices = (new InvoicesExportFormat($invoices))->exportToPdf($pdfService);
        $pdfService->mergeFiles($pdfInvoices, $path);

        return SaasAccount::whereSlug('circlelink-health')
            ->firstOrFail()
            ->addMedia($path)
            ->toMediaCollection("$downloadName");
    }
}
