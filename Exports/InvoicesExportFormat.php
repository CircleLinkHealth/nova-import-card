<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Exports;

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
     *
     * @param NurseInvoice $invoice
     */
    public function __construct(Collection $invoices)
    {
        $this->invoices = $invoices;
    }

    public function exportToPdf(PdfService $pdfService)
    {
        return  $this->invoices->map(function ($invoice) use ($pdfService) {
            $invoice = $invoice['data']->first();
            $nurseUserId = Nurse::findOrFail($invoice->nurse_info_id)->user_id;
            $args = $this->getInvoiceArgs($invoice, $nurseUserId);

            return $pdfService->createPdfFromView('nurseinvoices::invoice-v3', array_merge($args, ['isPdf' => true]));
        })->toArray();
    }

    public function toCsvArray()
    {
        return  $this->invoices->map(function ($invoice) {
            $invoice = $invoice['data']->first();
            if (isset($invoice->invoice_data)) {
                $baseSalary = $invoice->invoice_data['baseSalary'];

                $payStructure = 'visit';
                if ($invoice->invoice_data['variablePay']) {
                    if ($invoice->invoice_data['fixedRatePay'] > $invoice->invoice_data['variableRatePay']) {
                        $payStructure = 'hourly';
                    }
                }

                return [
                    'Nurse'          => $invoice->invoice_data['nurseFullName'],
                    'Hour Total'     => 0 === $invoice->invoice_data['systemTimeInHours'] ? '-' : $invoice->invoice_data['systemTimeInHours'],
                    'Visit Total'    => 0 === $invoice->invoice_data['visitsCount'] ? '-' : $invoice->invoice_data['visitsCount'],
                    'Pay Structure'  => $payStructure,
                    'Visit Hour Pay' => 0 === $baseSalary ? '-' : "$$baseSalary",
                    'Extra Time'     => 0 === $invoice->invoice_data['addedTimeAmount'] ? '-' : $invoice->invoice_data['addedTimeAmount'],
                    'Bonus'          => 0 === $invoice->invoice_data['bonus'] ? '-' : $invoice->invoice_data['bonus'],
                    'Pay Total'      => 0 === $invoice->invoice_data['formattedInvoiceTotalAmount'] ? '-' : $invoice->invoice_data['formattedInvoiceTotalAmount'],
                ];
            }

            return [
            ];
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
