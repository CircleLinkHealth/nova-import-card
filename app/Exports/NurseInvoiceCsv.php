<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Traits\AttachableAsMedia;
use Carbon\Carbon;
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
     *
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
        $this->setFilename();
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $invoices = NurseInvoice::with('nurse.user')
            ->where('month_year', $this->date)
            ->get();

        $invoicesData = [];
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

    public function storeAndAttachMediaTo($model)
    {
        $filepath = 'exports/'.$this->getFilename();
        $this->store($filepath, 'storage');

        return $this->attachMediaTo($model, storage_path($filepath), "nurse_monthly_invoice_for_{$this->date->format('F Y')}");
    }
}
