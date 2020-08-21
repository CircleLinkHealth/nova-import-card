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
            $nurseUserId = Nurse::findOrFail($invoice->nurse_info_id)->user_id;
            $args = $this->getInvoiceArgs($invoice, $nurseUserId);

            return $pdfService->createPdfFromView('nurseinvoices::invoice-v3', array_merge($args, ['isPdf' => true]));
        })->toArray();
    }

    public function toCsvArray()
    {
        return  $this->invoices->map(function ($invoice) {
            if ( ! isset($invoice->first()->invoice_data)) {
                return [
                    'Nurse'          => 'n/a',
                    'Hour Total'     => 'n/a',
                    'Visit Total'    => 'n/a',
                    'Pay Structure'  => 'n/a',
                    'Visit Hour Pay' => 'n/a',
                    'Extra Time'     => 'n/a',
                    'Bonus'          => 'n/a',
                    'Pay Total'      => 'n/a',
                ];
            }
            $invoice = $invoice->first()->invoice_data;
            $baseSalary = $invoice['baseSalary'];

            $payStructure = 'visit';
            if ( ! $invoice['variablePay'] || $invoice['changedToFixedRateBecauseItYieldedMore']) {
                $payStructure = 'hourly';
            }

            return [
                'Nurse'          => $this->sanitizedInvoiceData('nurseFullName', $invoice),
                'Hour Total'     => $this->sanitizedInvoiceData('systemTimeInHours', $invoice),
                'Visit Total'    => $this->sanitizedInvoiceData('visitsCount', $invoice),
                'Pay Structure'  => $payStructure,
                'Visit Hour Pay' => 0 === $baseSalary ? '-' : "$$baseSalary",
                'Extra Time'     => $this->sanitizedInvoiceData('addedTimeAmount', $invoice),
                'Bonus'          => $this->sanitizedInvoiceData('bonus', $invoice),
                'Pay Total'      => $this->sanitizedInvoiceData('formattedInvoiceTotalAmount', $invoice),
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
    
    /**
     * @param string $index
     * @param array $array
     * @return mixed|string
     */
    private function sanitizedInvoiceData(string $index, array $array)
    {
        if ( ! isset($array[$index]) || 0 === $array[$index]) {
            return '-';
        }
    
        return $array[$index];
    }
}
