<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;
use CircleLinkHealth\NurseInvoices\Http\Requests\ShowNurseInvoice;
use CircleLinkHealth\NurseInvoices\Http\Requests\StoreNurseInvoiceApproval;
use CircleLinkHealth\NurseInvoices\Http\Requests\StoreNurseInvoiceDispute;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceReviewController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function approveInvoice(StoreNurseInvoiceApproval $request)
    {
        $id = $request->input('invoiceId');

        $invoice = NurseInvoice::findOrFail($id);

        $invoice->is_nurse_approved = true;
        $invoice->nurse_approved_at = now();
        $invoice->save();

        return $this->ok();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function disputeInvoice(StoreNurseInvoiceDispute $request)
    {
        $id     = $request->input('invoiceId');
        $reason = $request->input('reason');

        NurseInvoice::findOrFail($id)
            ->dispute()
            ->create(
                [
                    'reason'  => $reason,
                    'user_id' => auth()->id(),
                ]
                    );

        return $this->ok();
    }

    /**
     * @return Factory|View
     */
    public function reviewInvoice(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth()->subMonth();

        $invoice = NurseInvoice::where('month_year', $startDate)
            ->with(['dispute.resolver'])
            ->ofNurses(auth()->id())
            ->firstOrNew([]);

        $deadline    = new NurseInvoiceDisputeDeadline($startDate);
        $invoiceData = $invoice->invoice_data ?? [];
        $args        = array_merge(
            [
                'invoiceId'              => $invoice->id,
                'dispute'                => $invoice->dispute,
                'invoice'                => $invoice,
                'shouldShowDisputeForm'  => auth()->user()->shouldShowInvoiceReviewButton(),
                'disputeDeadline'        => $deadline->deadline()->setTimezone(auth()->user()->timezone),
                'disputeDeadlineWarning' => $deadline->warning(),
            ],
            $invoiceData
        );

        if ('web' === $request->input('view')) {
            return view('nurseinvoices::invoice-v2', array_merge($args, ['isPdf' => true]));
        }

        return view(
            'nurseinvoices::reviewInvoice',
            $args
        );
    }

    public function show(ShowNurseInvoice $request, $nurseInfoId, $invoiceId)
    {
        $auth = auth()->user();

        $invoice = NurseInvoice::where('id', $invoiceId)
            ->with(['dispute.resolver'])
            ->where('nurse_info_id', $nurseInfoId)
            ->firstOrFail();

        $deadline    = new NurseInvoiceDisputeDeadline($invoice->month_year);
        $invoiceData = $invoice->invoice_data ?? [];
        $args        = array_merge(
            [
                'invoiceId'              => $invoice->id,
                'dispute'                => $invoice->dispute,
                'invoice'                => $invoice,
                'shouldShowDisputeForm'  => $auth->isAdmin() ? false : $auth->shouldShowInvoiceReviewButton(),
                'disputeDeadline'        => $deadline->deadline()->setTimezone($auth->timezone),
                'disputeDeadlineWarning' => $deadline->warning(),
            ],
            $invoiceData
        );

        if ('web' === $request->input('view')) {
            return view('nurseinvoices::invoice-v2', array_merge($args, ['isPdf' => true]));
        }

        return view(
            'nurseinvoices::reviewInvoice',
            $args
        );
    }
}
