<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Traits\AttachableAsMedia;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NurseInvoiceCsv implements FromCollection, Responsable, WithHeadings
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
     *
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
        $this->setFilename();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $invoices = NurseInvoice::with('nurse.user')
            ->where('month_year', $this->date)
            ->get();

        $invoicesData = collect();
        foreach ($invoices as $invoice) {
            $invoicesData[] = [
                'name'         => $invoice->nurse->user->display_name,
                'month'        => $this->date->format('F Y'),
                'baseSalary'   => $invoice->invoice_data['formattedInvoiceTotalAmount'],
                'bonuses'      => $invoice->invoice_data['bonus'],
                'totalPayable' => $invoice->invoice_data['invoiceTotalAmount'], //@todo:get the correct value here
            ];
        }

        return $invoicesData;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Name',
            'Month/Year',
            'Base Salary',
            'Bonuses',
            'Total payable amount',
        ];
    }

    /**
     * @param string $filename
     *
     * @return NurseInvoiceCsv
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

    /**
     * @param $invoicesData
     *
     * @return Collection
     */
    private function row($invoicesData): Collection
    {
        foreach ($invoicesData as $invoiceData) {
            return collect(
                [
                    'Name'                 => $invoiceData['name'],
                    'Month/Year'           => $invoiceData['month'],
                    'Base Salary'          => $invoiceData['baseSalary'],
                    'Bonuses'              => $invoiceData['bonuses'],
                    'Total payable amount' => $invoiceData['totalPayable'],
                ]
            );
        }
    }
}
