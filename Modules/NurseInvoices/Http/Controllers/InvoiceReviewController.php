<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Exceptions\FileNotFoundException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\Dispute;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceReviewController extends Controller
{
    /**
     * @param Request $request
     */
    public function disputeInvoice(Request $request)
    {
        $dispute = new Dispute();
        $dispute->disputable()->create(
            [//@todo:disputable_type and Id dont get saved
                'reason'  => $request->input('reason'),
                'user_id' => auth()->id(),
            ]
        );
    }

    /**
     * @throws FileNotFoundException
     *
     * @return \Illuminate\Contracts\View\Factory|View
     */
    public function reviewInvoice()
    {
        $startDate = Carbon::now()->subMonth(1)->startOfMonth();
        $invoice   = NurseInvoice::where('month_year', $startDate)
            ->ofNurses(auth()->id())
            ->first();

        $invoiceData = optional($invoice)->invoice_data;

        if ( ! $invoiceData) {
            throw new FileNotFoundException('Invoice data not found');
        }

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
                //                @todo:get changedToFixedRateBecauseItYieldedMore.
            ]
        );
    }
}
