<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Nurseinvoices;

use App\Exports\NurseInvoicesExport;
use App\Services\AttachDisputesToTimePerDay;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Http\Controllers\InvoiceReviewController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GenerateInvoiceDownload
{
    /**
     * @var
     */
    private $date;
    private $downloadFormat;

    /**
     * @var Collection
     */
    private $invoicesPerPractice;

    /**
     * GenerateInvoiceDownload constructor.
     */
    public function __construct(Collection $invoicesPerPractice, string $downloadFormat, \Carbon\Carbon $date)
    {
        $this->invoicesPerPractice = $invoicesPerPractice;
        $this->date                = $date;
        $this->downloadFormat      = $downloadFormat;
    }

    public function generateExportableInvoices()
    {
        if (NurseInvoice::PDF_DOWNLOAD_FORMAT === $this->downloadFormat) {
            return $this->generateInvoicePdf();
        }

        if (NurseInvoice::CSV_DOWNLOAD_FORMAT === $this->downloadFormat) {
            return $this->generateInvoiceCsv();
        }
    }

    public function generateInvoiceCsv()
    {
        $month    = Carbon::parse($this->date)->toDateString();
        $mediaIds = [];
        foreach ($this->invoicesPerPractice as $practiceId => $invoices) {
            $practice     = Practice::findOrFail($practiceId);
            $downloadName = trim($practice->display_name).'-'.$month;
            $mediaIds[]   = (new FromArray(
                "$downloadName.csv",
                (new NurseInvoicesExport(
                    $invoices
                ))->collection(),
                [
                ]
            ))->storeAndAttachMediaTo($practice, "patient_report_for_{$month}");
        }

        return $mediaIds;
    }

    /**
     * @return array
     */
    public function generateInvoicePdf()
    {
        $invoicesForMonth = Carbon::parse($this->date)->toDateString();
        $downloadName     = trim("$invoicesForMonth").'-pdf'.'-'.now()->toDateString();

        $pdfInvoices = $this->makeInvoicesPdf($downloadName);

        return [
            'invoice_url' => $pdfInvoices->getUrl(),
            'mediaIds'    => [$pdfInvoices->id],
        ];
    }

    /**
     * @param $invoice
     *
     * @return array
     */
    private function getInvoiceArgs($invoice, int $nurseUserId)
    {
        return array_merge(
            [
                'invoiceId'                => $invoice->id,
                'disputes'                 => $invoice->disputes,
                'invoice'                  => $invoice,
                'shouldShowDisputeForm'    => false,
                'isUserAuthToDailyDispute' => false,
                'canBeDisputed'            => false,
                'monthInvoiceMap'          => (new InvoiceReviewController(new AttachDisputesToTimePerDay()))->getNurseInvoiceMap($nurseUserId),
            ],
            $invoice->invoice_data ?? [],
        );
    }

    /**
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    private function makeInvoicesPdf(string $downloadName)
    {
        $pdfService = app(PdfService::class);

        \Storage::disk('storage')
            ->makeDirectory('download');

        $path = storage_path("download/$downloadName.pdf");

        $downloadableInvoices = [];
        foreach ($this->invoicesPerPractice as $invoice) {
//            @todo:fix me
            $nurseUserId            = Nurse::findOrFail($invoice->nurse_info_id)->user_id;
            $args                   = $this->getInvoiceArgs($invoice, $nurseUserId);
            $downloadableInvoices[] = $pdfService->createPdfFromView('nurseinvoices::reviewInvoice', $args);
        }

        $pdfService->mergeFiles($downloadableInvoices, $path);

        return SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($path)
            ->toMediaCollection("pdf_invoices_for_{$this->date->toDateString()}");
    }
}
