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
        $practiceNamesForUi    = implode(',', $this->getPracticesNames($practices));
        $downloadFormatsValues = $this->getDownloadFormats($downloadFormats);
        $month                 = Carbon::parse($date['value'])->startOfMonth();
        $monthToString         = Carbon::parse($month)->toDateString();

        ExportAndDispatchInvoices::dispatch($practiceIds, $downloadFormatsValues, $month, $auth)->onQueue('low');

        return response()->json(
            [
                'message' => "We are exporting invoices for $monthToString, for the following practices:$practiceNamesForUi.
                 Will be send to your email when exporting is done!",
            ],
            200
        );
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        return Practice::active()
            ->with('nurses')
            ->authUserCanAccess()
            ->whereHas('nurses')
            ->select(DB::raw('id as value'), DB::raw('display_name as label'))
            ->get();
    }

    /**
     * @return array
     */
    private function getDownloadFormats(array $downloadFormats)
    {
        return collect($downloadFormats)->pluck('label')->toArray();
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
