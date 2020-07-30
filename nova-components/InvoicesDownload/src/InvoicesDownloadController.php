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

        $downloadFormats = $request->input('downloadFormats');
        $date            = $request->input('date');

        if (empty($date)) {
            throw new \Exception('Month to download invoices for is required');
        }

        if (empty($downloadFormats)) {
            throw new \Exception('Month to download invoices for is required');
        }

        $downloadFormatsValues = $this->getDownloadFormats($downloadFormats);
        $month                 = Carbon::parse($date['label'])->startOfMonth();
        $monthToHuman          = Carbon::parse($month)->format('M-Y');

        ExportAndDispatchInvoices::dispatch($downloadFormatsValues, $month, $auth)->onQueue('low');

        return response()->json(
            [
                'message' => "We are exporting invoices for $monthToHuman. An email will be send to you when exporting is done!",
            ],
            200
        );
    }

    /**
     * @return array
     */
    private function getDownloadFormats(array $downloadFormats)
    {
        return collect($downloadFormats)->pluck('value')->toArray();
    }

    /**
     * @return array
     */
    private function getPracticesIds(array $practices)
    {
        return collect($practices)->pluck('value')->toArray();
    }

    /**
     * @return array
     */
    private function getPracticesNames(array $practices)
    {
        return  collect($practices)->pluck('label')->toArray();
    }
}
