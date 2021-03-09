<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Carbon\Carbon;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;

class InvoicesDownloadController
{
    public function downloadInvoices(InvoicesDownloadRequest $request)
    {
        $downloadFormat = $request->input('downloadFormat');
        $date           = $request->input('date');

        $month        = Carbon::parse($date['label'])->startOfMonth();
        $monthToHuman = Carbon::parse($month)->format('M-Y');

        ExportAndDispatchInvoices::dispatch($downloadFormat['value'], $month, auth()->id())->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));

        return response()->json(
            [
                'message' => "We are exporting invoices for $monthToHuman. An email will be sent to you when exporting is done!",
            ],
            200
        );
    }
}
