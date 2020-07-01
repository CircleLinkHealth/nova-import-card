<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Support\Collection;

class NurseInvoicesExport
{
    /**
     * @var NurseInvoice
     */
    private $invoices;

    /**
     * NurseInvoicesExport constructor.
     * @param NurseInvoice $invoice
     */
    public function __construct(Collection $invoices)
    {
        $this->invoices = $invoices;
    }

    public function collection()
    {
        return  $this->invoices->map(function ($invoice) {
            if ( ! empty($invoice->invoice_data)) {
                return [
                    'extra_time' => 0 === $invoice->invoice_data['addedTimeAmount'] ? '-' : $invoice->invoice_data['addedTimeAmount'],
                    'bonus'      => 0 === $invoice->invoice_data['bonus'] ? '-' : $invoice->invoice_data['bonus'],
                ];
            }

            return [];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Extra Time',
            'Bonus',
        ];
    }
}
