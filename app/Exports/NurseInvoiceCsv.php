<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use Carbon\Carbon;
use CircleLinkHealth\Core\Traits\AttachableAsMedia;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NurseInvoiceCsv implements FromArray, Responsable, WithHeadings
{
    use AttachableAsMedia;
    use Exportable;
    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var
     */
    protected $filename;

    /**
     * NurseInvoiceCsv constructor.
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
        $this->setFilename();
    }

    /**
     * Collect all invoices for given date from nurse_invoices table.
     */
    public function array(): array
    {
        $invoices = $this->invoicesQuery()
            ->get();

        $invoicesData = [];
        foreach ($invoices as $invoice) {
            $invoicesData[] = [
                'name'         => $invoice->nurse->user->display_name,
                'month'        => $this->date->format('F Y'),
                'baseFees'     => $invoice->invoice_data['baseSalary'],
                'bonuses'      => $invoice->invoice_data['bonus'],
                'extraTime'    => $invoice->invoice_data['addedTimeAmount'],
                'totalPayable' => $invoice->invoice_data['invoiceTotalAmount'],
            ];
        }

        return $invoicesData;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Month/Year',
            'Base Fees',
            'Bonuses',
            'Extra Time Fees',
            'Total payable amount',
        ];
    }

    public function invoicesQuery()
    {
        return NurseInvoice::with(
            [
                'nurse.user' => function ($q) {
                    $q->withTrashed();
                },
            ]
        )
            ->where('month_year', $this->date)
            ->whereHas('nurse.user', function ($q) {
                $q->withTrashed();
            });
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename = null): NurseInvoiceCsv
    {
        if ( ! $filename) {
            $dateString = $this->date->format('F Y');
            $filename   = 'Nurse_Invoices_Csv';

            $this->filename = "{$filename}_{$dateString}.csv";

            return $this;
        }

        $this->filename = $filename;

        return $this;
    }

    public function storeAndAttachMediaTo($model)
    {
        $filepath = 'exports/'.$this->getFilename();

        $this->store($filepath, 'storage');

        return $this->attachMediaTo(
            $model,
            storage_path($filepath),
            "nurse_monthly_invoices_for_{$this->date->format('F Y')}"
        );
    }
}
