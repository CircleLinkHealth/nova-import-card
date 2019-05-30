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

class NurseInvoiceCsv implements FromCollection, Responsable
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
        //todo: why do i have to call collection() method to work?
        $this->collection();
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
            $data[$invoice->nurse->user->id] = [
                'name'         => $invoice->nurse->user->display_name,
                'month'        => $this->date->format('F Y'),
                'baseSalary'   => $invoice->invoice_data['formattedInvoiceTotalAmount'],
                'bonuses'      => $invoice->invoice_data['bonus'],
                'totalPayable' => $invoice->invoice_data['invoiceTotalAmount'],
            ];
        }

        return $invoicesData;
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
}
