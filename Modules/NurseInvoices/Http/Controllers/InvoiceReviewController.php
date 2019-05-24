<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;

class InvoiceReviewController extends Controller
{
    public function reviewInvoice()
    {
        $startDate = Carbon::now()->subMonth(8)->startOfMonth();

        $invoice = NurseInvoice::where([
            ['month_year', $startDate],
        ])->ofNurses(auth()->id())
            ->first();

        $invoiceData = $invoice->invoice_data;

        return view('nurseinvoices::reviewInvoice', compact('invoiceData'));
    }
}
