<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSuggestedTime;
use App\Services\NurseInvoiceDailyDisputeTimeService;
use Illuminate\Http\JsonResponse;

class NurseInvoiceDailyDisputesController extends Controller
{
    /**
     * @var NurseInvoiceDailyDisputeTimeService
     */
    private $service;

    /**
     * NurseInvoiceDailyDisputesController constructor.
     */
    public function __construct(NurseInvoiceDailyDisputeTimeService $service)
    {
        $this->service = $service;
    }

    /**
     * Care Coach might decide to delete the dispute.
     *
     * @param $invoiceId
     * @param $disputedDay
     *
     * @return JsonResponse
     */
    public function deleteSuggestedWorkTime($invoiceId, $disputedDay)
    {
        $this->service->deleteDispute($invoiceId, $disputedDay);

        return response()->json([
            'deleted'     => true,
            'disputedDay' => $disputedDay,
        ], 200);
    }

    /**
     * @return JsonResponse
     */
    public function storeSuggestedWorkTime(StoreSuggestedTime $request)
    {
        $input = $request->all();

        $disputeSuggestedTime = $this->service->saveDispute($input);

        if ( ! $disputeSuggestedTime) {
            return response()->json(['errors' => 'Dispute was not created'], 400);
        }

        return response()->json([
            'created' => true,
            'time'    => $disputeSuggestedTime,
        ], 200);
    }
}
