<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Http\Requests\DownloadInvoicesNova;
use CircleLinkHealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;

class InvoicesDownloadController
{
    public function downloadInvoices(DownloadInvoicesNova $request)
    {
        $auth = auth()->user();

        $downloadFormat = $request->input('downloadFormat');
        $date           = $request->input('date');

        $month        = Carbon::parse($date['label'])->startOfMonth();
        $monthToHuman = Carbon::parse($month)->format('M-Y');

        ExportAndDispatchInvoices::dispatch($downloadFormat['value'], $month, $auth)->onQueue('low');

        return response()->json(
            [
                'message' => "We are exporting invoices for $monthToHuman. An email will be sent to you when exporting is done!",
            ],
            200
        );
    }
}
