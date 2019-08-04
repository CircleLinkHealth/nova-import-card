<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AttachDisputesToTimePerDay;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;
use CircleLinkHealth\NurseInvoices\Http\Requests\AdminShowNurseInvoice;
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
     * @var AttachDisputesToTimePerDay
     */
    private $attachDisputes;

    /**
     * InvoiceReviewController constructor.
     *
     * @param AttachDisputesToTimePerDay $attachDisputes
     */
    public function __construct(AttachDisputesToTimePerDay $attachDisputes)
    {
        $this->attachDisputes = $attachDisputes;
    }

    /**
     * @param AdminShowNurseInvoice $request
     * @param $nurseInfoId
     * @param $invoiceId
     *
     * @return Factory|View
     */
    public function adminShow(AdminShowNurseInvoice $request, $nurseInfoId, $invoiceId)
    {
        $invoice = NurseInvoice::where('id', $invoiceId)
            ->with(['dispute.resolver'])
            ->where('nurse_info_id', $nurseInfoId)
            ->firstOrFail();

        return $this->invoice($request, $invoice);
    }

    /**
     * @param StoreNurseInvoiceApproval $request
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
     * @param Request $request
     *
     * @return Factory|View
     */
    public function reviewInvoice(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth()->subMonth();

        $invoice = NurseInvoice::where('month_year', $startDate)
            ->with(
                [
                    'dispute.resolver',
                    'dailyDisputes',
                ]
            )
            ->ofNurses(auth()->id())
            ->firstOrNew([]);

        return $this->invoice($request, $invoice);
    }

    /**
     * @param ShowNurseInvoice $request
     *
     * @return Factory|View
     */
    public function show(ShowNurseInvoice $request)
    {
        $invoice = NurseInvoice::where('id', $request->input('invoice_id'))
            ->with(['dispute.resolver'])
            ->firstOrFail();

        return $this->invoice($request, $invoice);
    }

    private function canBeDisputed(NurseInvoice $invoice, Carbon $deadline)
    {
        if ( ! $invoice->month_year) {
            return false;
        }

        return null === $invoice->dispute && ! $invoice->is_nurse_approved && Carbon::now()->lte($deadline) && Carbon::now()->gte($invoice->month_year->copy()->addMonth());
    }

    private function invoice(Request $request, NurseInvoice $invoice)
    {
        $auth                    = auth()->user();
        $invoiceDataWithDisputes = $this->attachDisputes->putDisputesToTimePerDay($invoice);
        $deadline                = new NurseInvoiceDisputeDeadline($invoice->month_year ?? Carbon::now()->subMonth());
        $invoiceData             = $invoiceDataWithDisputes ?? [];

        $args = array_merge(
            [
                'invoiceId'              => $invoice->id,
                'dispute'                => $invoice->dispute,
                'invoice'                => $invoice,
                'shouldShowDisputeForm'  => $auth->isAdmin() ? false : $this->canBeDisputed($invoice, $deadline->deadline()),
                'disputeDeadline'        => $deadline->deadline()->setTimezone($auth->timezone),
                'disputeDeadlineWarning' => $deadline->warning(),
                'monthInvoiceMap'        => NurseInvoice::where('nurse_info_id', $invoice->nurse_info_id)->pluck('month_year', 'id'),
            ],
            $invoiceData
        );

        if ('web' === $request->input('view')) {
            return view('nurseinvoices::invoice-v3', array_merge($args, ['isPdf' => true]));
        }

        return view('nurseinvoices::reviewInvoice', $args);
    }
}
