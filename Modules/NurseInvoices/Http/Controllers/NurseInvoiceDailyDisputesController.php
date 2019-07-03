<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSuggestedTime;
use App\Services\NurseInvoiceDailyDisputeTimeService;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class NurseInvoiceDailyDisputesController extends Controller
{
    /**
     * @var \App\Services\NurseInvoiceDailyDisputeTimeService
     */
    private $service;

    /**
     * NurseInvoiceDailyDisputesController constructor.
     *
     * @param \App\Services\NurseInvoiceDailyDisputeTimeService $service
     */
    public function __construct(NurseInvoiceDailyDisputeTimeService $service)
    {
        $this->service = $service;
    }

    public function deleteDispute($invoiceId, $disputedDay)
    {
        NurseInvoiceDailyDispute::where([
            ['invoice_id', $invoiceId],
            ['disputed_day', $disputedDay],
        ])->delete();

        return response()->json([
            'deleted'     => true,
            'disputedDay' => $disputedDay,
        ], 200);
    }

    /**
     * @param StoreSuggestedTime $request
     *
     * @return JsonResponse
     */
    public function storeSuggestedWorkTime(StoreSuggestedTime $request)
    {
        $input = $request->all();

        $suggestedTime = $this->service->storeDisputedTime($input);

        if ( ! $suggestedTime) {
            return response()->json(['errors' => 'Dispute was not created'], 400);
        }

        return response()->json([
            'created' => true,
            'time'    => $suggestedTime,
        ], 200);
    }
}
