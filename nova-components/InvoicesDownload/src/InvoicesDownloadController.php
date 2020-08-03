<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;
use Laravel\Nova\Http\Requests\NovaRequest;

class InvoicesDownloadController
{
    public function downloadInvoices(NovaRequest $request)
    {
        $auth = auth()->user() ?? null;

        if (is_null($auth)) {
            throw new \Exception('Auth user not found');
        }

        $downloadFormat = $request->input('downloadFormat');
        $date           = $request->input('date');

        if (empty($date)) {
            throw new \Exception('Month to download invoices for is required');
        }

        if (empty($downloadFormat)) {
            throw new \Exception('Month to download invoices for is required');
        }

        $month        = Carbon::parse($date['label'])->startOfMonth();
        $monthToHuman = Carbon::parse($month)->format('M-Y');

        ExportAndDispatchInvoices::dispatch($downloadFormat['value'], $month, $auth)->onQueue('low');

        return response()->json(
            [
                'message' => "We are exporting invoices for $monthToHuman. An email will be send to you when exporting is done!",
            ],
            200
        );
    }
}
