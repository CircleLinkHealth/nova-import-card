<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Carbon\Carbon;
use Circlelinkhealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;

class InvoicesDownloadController
{
    public function downloadInvoices(InvoicesDownloadRequest $request)
    {
        $auth = auth()->user();

        $downloadFormat = $request->input('downloadFormat');
        $date           = $request->input('date');

        $month        = Carbon::parse($date['label'])->startOfMonth();
        $monthToHuman = Carbon::parse($month)->format('M-Y');

        ExportAndDispatchInvoices::dispatch($downloadFormat['value'], $month, $auth);

        return response()->json(
            [
                'message' => "We are exporting invoices for $monthToHuman. An email will be sent to you when exporting is done!",
            ],
            200
        );
    }
}
