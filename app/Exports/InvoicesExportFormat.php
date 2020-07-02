<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Services\AttachDisputesToTimePerDay;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Http\Controllers\InvoiceReviewController;
use Illuminate\Support\Collection;

class InvoicesExportFormat
{
    /**
     * @var NurseInvoice
     */
    private $invoices;

    /**
     * InvoicesExportFormat constructor.
     * @param NurseInvoice $invoice
     */
    public function __construct(Collection $invoices)
    {
        $this->invoices = $invoices;
    }

    public function exportToPdf(PdfService $pdfService)
    {
        return  $this->invoices->map(function ($invoice) use ($pdfService) {
            $invoice = $invoice->first();
            $nurseUserId = Nurse::findOrFail($invoice->nurse_info_id)->user_id;
            $args = $this->getInvoiceArgs($invoice, $nurseUserId);

            return $pdfService->createPdfFromView('nurseinvoices::reviewInvoice', $args);
        })->toArray();
    }

    public static function headings(): array
    {
//        Keep the same order as toCsvArray() keys.
        return [
            'Extra Time',
            'Bonus',
        ];
    }

    public function toCsvArray()
    {
        return  $this->invoices->map(function ($invoice) {
            $invoice = $invoice->first();
            if ( ! empty($invoice)) {
                return [
                    'extra_time' => 0 === $invoice->invoice_data['addedTimeAmount'] ? '-' : $invoice->invoice_data['addedTimeAmount'],
                    'bonus'      => 0 === $invoice->invoice_data['bonus'] ? '-' : $invoice->invoice_data['bonus'],
                ];
            }

            return [];
        })->toArray();
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
}
