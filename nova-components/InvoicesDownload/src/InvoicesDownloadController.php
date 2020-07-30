<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;
use Illuminate\Support\Facades\DB;
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
        $monthToString         = Carbon::parse($month)->toDateString();

        ExportAndDispatchInvoices::dispatch($downloadFormatsValues, $month, $auth)->onQueue('low');

        return response()->json(
            [
                'message' => "We are exporting invoices for $monthToString. An email will be send to you when exporting is done!",
            ],
            200
        );
    }

    /**
     * @return mixed
     */
    public function handle()
    {
//        return Practice::active()
//            ->with('nurses')
//            ->authUserCanAccess()
//            ->whereHas('nurses')
//            ->select(DB::raw('id as value'), DB::raw('display_name as label'))
//            ->get();
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
