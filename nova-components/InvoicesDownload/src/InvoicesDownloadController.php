<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
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
        $practices       = $request->input('practices');
        $date            = $request->input('date');

        if (empty($practices)) {
            throw new \Exception('Practices field is required');
        }

        if (empty($date)) {
            throw new \Exception('Month to download invoices for is required');
        }

        if (empty($downloadFormats)) {
            throw new \Exception('Month to download invoices for is required');
        }

        $practiceIds           = $this->getPracticesIds($practices);
        $downloadFormatsValues = $this->getDownloadFormats($downloadFormats);
        $month                 = Carbon::parse($date['value'])->startOfMonth();

        ExportAndDispatchInvoices::dispatch($practiceIds, $downloadFormatsValues, $month, $auth)->onQueue('low');
    }

    public function handle()
    {
        return  Practice::active()
//            ->authUserCanAccess()
            ->select('id', 'display_name')
            ->get()
            ->transform(function ($practice) {
                return [
                    'label' => $practice->display_name,
                    'value' => $practice->id,
                ];
            });
    }

    private function getDownloadFormats(array $downloadFormats)
    {
        $formats = [];
        foreach ($downloadFormats as $format) {
            $formats[] = $format['value'];
        }

        return $formats;
    }

    /**
     * @param $practices
     * @return array
     */
    private function getPracticesIds($practices)
    {
        $practiceIds = [];

        foreach ($practices as $practice) {
            $practiceIds[] = $practice['value'];
        }

        return $practiceIds;
    }
}
