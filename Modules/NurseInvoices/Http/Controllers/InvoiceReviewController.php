<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Exceptions\FileNotFoundException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Http\Requests\StoreNurseInvoiceDispute;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceReviewController extends Controller
{
    /**
     * @param Request $request
     */
    public function disputeInvoice(StoreNurseInvoiceDispute $request)
    {
        $id     = $request->input('invoiceId');
        $reason = $request->input('reason');

        NurseInvoice::findOrFail($id)
            ->disputes()
            ->create(
                [
                    'reason'  => $reason,
                    'user_id' => auth()->id(),
                ]
            );

        //@todo: wip. put in nurse invoice service class
        \Cache::tags(['shouldShowInvoiceReviewButton'])->forget('nurse_invoice_show_approve:user_id:'.auth()->id());

        return $this->ok();
    }

    /**
     * @throws FileNotFoundException
     *
     * @return \Illuminate\Contracts\View\Factory|View
     */
    public function reviewInvoice()
    {
        $startDate = Carbon::now()->subMonth(1)->startOfMonth();

        $invoice = NurseInvoice::where('month_year', $startDate)
            ->undisputed()
            ->ofNurses(auth()->id())
            ->first();

        $invoiceData = optional($invoice)->invoice_data;

        if ( ! $invoiceData) {
            throw new FileNotFoundException('Invoice data not found');
        }

        return view(
            'nurseinvoices::reviewInvoice',
            array_merge(['invoiceId' => $invoice->id], $invoiceData)
        );
    }
}
