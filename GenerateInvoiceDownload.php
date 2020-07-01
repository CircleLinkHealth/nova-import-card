<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Nurseinvoices;

use App\Services\AttachDisputesToTimePerDay;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\NurseInvoices\Http\Controllers\InvoiceReviewController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GenerateInvoiceDownload
{
    /**
     * @var
     */
    private $date;

    /**
     * @var Collection
     */
    private $invoices;

    /**
     * GenerateInvoiceDownload constructor.
     *
     * @param $date
     */
    public function __construct(Collection $invoices, $date)
    {
        $this->invoices = $invoices;
        $this->date     = $date;
    }

    public function generateInvoiceCsv()
    {
        $rows = [];
        foreach ($this->invoices as $invoice) {
            $rows[] = $this->makeRow($invoice);
        }

        $x = 1;
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

    private function makeInvoicesCsv(string $downloadName, object $invoice)
    {
        \Storage::disk('storage')
            ->makeDirectory('download');

        $path = storage_path("download/$downloadName");

        $csv = (new FromArray(
            "$downloadName.csv",
            (new NurseInvoiceCsvGenerator($invoice))->toCsvArray(),
            [
            ]
        ));
//        $x = 1;
//
//        \Excel::store($csv, $path, 's3');

//
//        return SaasAccount::whereSlug('circlelink-health')
//            ->first()
//            ->addMedia($path)
//            ->toMediaCollection("invoices_for_{$this->date->toDateString()}_xlsx");
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
        foreach ($this->invoices as $invoice) {
            $nurseUserId            = Nurse::findOrFail($invoice->nurse_info_id)->user_id;
            $args                   = $this->getInvoiceArgs($invoice, $nurseUserId);
            $downloadableInvoices[] = $pdfService->createPdfFromView('nurseinvoices::reviewInvoice', $args);
        }

        $pdfService->mergeFiles($downloadableInvoices, $path);

        return SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($path)
            ->toMediaCollection("invoice_for_{$this->date->toDateString()}");
    }

    private function makeRow($invoice)
    {
        $invoicesForMonth = Carbon::parse($this->date)->toDateString();
        $downloadName     = trim("$invoicesForMonth").'-csv'.'-'.now()->toDateString();

        return $this->makeInvoicesCsv($downloadName, $invoice);
    }

    private function toCsvArray(object $invoice)
    {
        $invoiceData = $invoice->invoice_data;

        if (empty($invoiceData)) {
            throw new \Exception("Invoice data for invoice id {$invoice->id} not found");
        }

//        if ($invoiceData['variablePay']) {
//            if (isset($invoiceData['altAlgoEnabled']) && ! $invoiceData['altAlgoEnabled']) {
//
//            }
//        }

        return [
            'extra_time' => $invoiceData['addedTimeAmount'],
            'bonus'      => $invoiceData['bonus'],
        ];
    }
}
