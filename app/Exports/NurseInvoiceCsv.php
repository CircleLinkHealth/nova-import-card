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

    public function __construct(Carbon $date)
    {
        $this->date = $date;
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
            $invoicesData[$invoice->nurse->user->id] = [
                'name'         => $invoice->nurse->user->display_name,
                'month'        => $this->date->format('F Y'),
                'baseSalary'   => $invoice->invoice_data['formattedInvoiceTotalAmount'],
                'bonuses'      => $invoice->invoice_data['bonus'],
                'totalPayable' => $invoice->invoice_data['invoiceTotalAmount'], //@todo:get the correct value here
            ];
        }

        return $this->row($invoicesData);
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
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        // TODO: Implement toResponse() method.
    }

    /**
     * @param $invoicesData
     *
     * @return Collection
     */
    private function row($invoicesData): Collection
    {
        return collect(
            [
                'Name'                 => $invoicesData[9521]['name'],
                'Month/Year'           => $invoicesData[9521]['month'],
                'Base Salary'          => $invoicesData[9521]['baseSalary'],
                'Bonuses'              => $invoicesData[9521]['bonuses'],
                'Total payable amount' => $invoicesData[9521]['totalPayable'],
            ]
        );
    }
}
