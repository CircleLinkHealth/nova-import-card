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
     * @var NurseInvoiceDisputeDeadline
     */
    private $nurseInvoiceDisputeDeadline;

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
     * @param $nurseUserId
     * @param $invoiceId
     *
     * @return Factory|View
     */
    public function adminShow(AdminShowNurseInvoice $request, $nurseUserId, $invoiceId)
    {
        $invoice = NurseInvoice::where('id', $invoiceId)
            ->with(['dispute.resolver', 'dailyDisputes'])
            ->ofNurses($nurseUserId)
            ->firstOrFail();

        $invoiceDataWithDisputes = $this->attachDisputes->putDisputesToTimePerDay($invoice);

        return $this->invoice($request, $nurseUserId, $invoice, $invoiceDataWithDisputes);
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
     * @param $nurseUserId
     * @param $auth
     *
     * @return bool
     */
    public function checkUserIfAuthToDispute($nurseUserId, $auth)
    {
        return $nurseUserId === $auth->id ? true : false;
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
                    'nurse',
                ]
            )
            ->ofNurses(auth()->id())
            ->firstOrNew([]);

        $invoiceDataWithDisputes = $this->attachDisputes->putDisputesToTimePerDay($invoice);

        return $this->invoice($request, auth()->id(), $invoice, $invoiceDataWithDisputes);
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

        $deadline = $this->getDisputesDeadline($invoice->month_year);

        if ($this->canBeDisputed($invoice, $deadline->deadline())) {
            return $this->reviewInvoice($request);
        }

        return $this->invoice($request, auth()->id(), $invoice, []);
    }

    private function canBeDisputed(NurseInvoice $invoice, Carbon $deadline)
    {
        if ( ! $invoice->month_year) {
            return false;
        }

        return null === $invoice->dispute && ! $invoice->is_nurse_approved && Carbon::now()->lte($deadline) && Carbon::now()->gte($invoice->month_year->copy()->addMonth());
    }

    private function getDisputesDeadline(Carbon $date)
    {
        if ( ! $this->nurseInvoiceDisputeDeadline) {
            $this->nurseInvoiceDisputeDeadline = new NurseInvoiceDisputeDeadline($date);
        }

        return $this->nurseInvoiceDisputeDeadline;
    }

    private function getNurseInvoiceMap(int $nurseUserId)
    {
        return NurseInvoice::ofNurses($nurseUserId)->pluck('month_year', 'id');
    }

    /**
     * @param Request      $request
     * @param int          $nurseUserId
     * @param NurseInvoice $invoice
     * @param array        $invoiceDataWithDisputes
     *
     * @return Factory|View
     */
    private function invoice(Request $request, int $nurseUserId, NurseInvoice $invoice, $invoiceDataWithDisputes = [])
    {
        $auth = auth()->user();

        $deadline = $this->getDisputesDeadline($invoice->month_year);

        if ( ! empty($invoiceDataWithDisputes)) {
            $invoiceData = $invoiceDataWithDisputes;
        } else {
            $invoiceData = $invoice->invoice_data ?? [];
        }
        $canBeDisputed = $this->canBeDisputed($invoice, $deadline->deadline());
        $args          = array_merge(
            [
                'invoiceId'              => $invoice->id,
                'dispute'                => $invoice->dispute,
                'invoice'                => $invoice,
                'shouldShowDisputeForm'  => $auth->isAdmin() ? false : $canBeDisputed,
                'disputeDeadline'        => $deadline->deadline()->setTimezone($auth->timezone),
                'disputeDeadlineWarning' => $deadline->warning(),
                'monthInvoiceMap'        => $this->getNurseInvoiceMap($nurseUserId),
            ],
            $invoiceData
        );

        //This is when viewing a to-be-rendered-as-pdf Report in html
        if ('web' === $request->input('view')) {
            return view('nurseinvoices::invoice-v3', array_merge($args, ['isPdf' => true]));
        }
        //We want to disable "daily dispute functionality" for admins who view invoice from superadmin page.
        $isUserAuthToDailyDispute = $this->checkUserIfAuthToDispute($nurseUserId, $auth);

        return view('nurseinvoices::reviewInvoice', $args)->with(['isUserAuthToDailyDispute' => $isUserAuthToDailyDispute, 'canBeDisputed' => $canBeDisputed]);
    }
}
