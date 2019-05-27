<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\Dispute;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Http\Request;

class InvoiceReviewController extends Controller
{
    public function disputeInvoice(Request $request)
    {
        $dispute = new Dispute();
        $dispute->disputable()->create(
            [
                'reason'  => $request->input('reason'),
                'user_id' => auth()->id(),
            ]
        );
    }

    public function reviewInvoice()
    {
        $startDate = Carbon::now()->subMonth(8)->startOfMonth();

        $invoice = NurseInvoice::where('month_year', $startDate)->ofNurses(auth()->id())
            ->first();

        $invoiceData = optional($invoice)->invoice_data;

        return view(
            'nurseinvoices::reviewInvoice',
            [
                'nurseFullName'       => $invoiceData['nurseFullName'],
                'startDate'           => $invoiceData['startDate'],
                'endDate'             => $invoiceData['endDate'],
                'hasAddedTime'        => $invoiceData['hasAddedTime'],
                'addedTime'           => $invoiceData['addedTime'],
                'addedTimeAmount'     => $invoiceData['addedTimeAmount'],
                'bonus'               => $invoiceData['bonus'],
                'totalBillableRate'   => $invoiceData['totalBillableRate'],
                'variablePay'         => $invoiceData['variablePay'],
                'nurseHighRate'       => $invoiceData['nurseHighRate'],
                'nurseLowRate'        => $invoiceData['nurseLowRate'],
                'systemTimeInMinutes' => $invoiceData['systemTimeInMinutes'],
                'systemTimeInHours'   => $invoiceData['systemTimeInHours'],
                'totalTimeTowardsCcm' => $invoiceData['totalTimeTowardsCcm'],
                'totalTimeAfterCcm'   => $invoiceData['totalTimeAfterCcm'],
                'timePerDay'          => $invoiceData['timePerDay'],
            ]
        );
    }
}
