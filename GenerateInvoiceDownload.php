<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Nurseinvoices;

use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Support\Collection;

class GenerateInvoiceDownload
{
    /**
     * @var string
     */
    private $downloadFormat;
    /**
     * @var Collection
     */
    private $invoices;

    /**
     * GenerateInvoiceDownload constructor.
     */
    public function __construct(Collection $invoices, string $downloadFormat)
    {
        $this->invoices       = $invoices;
        $this->downloadFormat = $downloadFormat;
    }

    public function generateInvoicePdf()
    {
        $downloadName = trim('kolos').'-'.now()->toDateString().'-invoice';

        $pdfInvoice = $this->makeInvoicesPdf($downloadName);

        return [
            'invoice_url' => $pdfInvoice->getUrl(),
            'mediaIds'    => [$pdfInvoice->id],
        ];
    }

    private function makeInvoicesPdf(string $downloadName)
    {
        $pdfService = app(PdfService::class);

        \Storage::disk('storage')
            ->makeDirectory('download');

        $path = storage_path("download/${$downloadName}.pdf");

        foreach ($this->invoices as $invoice) {
            $args = array_merge(
                [
                    'invoiceId'             => $invoice->id,
                    'disputes'              => $invoice->disputes,
                    'invoice'               => $invoice,
                    'shouldShowDisputeForm' => false,
                    //                    'disputeDeadline'        => $deadline->deadline()->setTimezone($auth->timezone),
                    //                    'disputeDeadlineWarning' => $deadline->warning(),
                    //                    'monthInvoiceMap' => $this->getNurseInvoiceMap($nurseUserId),
                ],
                $invoice->invoice_data ?? [],
            );

            $pdf = $pdfService->createPdfFromView('nurseinvoices::reviewInvoice', $args, $path);
        }
        $month = '2020-06-01';

        return SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($path)
            ->toMediaCollection("invoice_for_{$month}");
    }
}
