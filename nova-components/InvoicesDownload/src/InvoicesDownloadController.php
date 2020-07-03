<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;
use Laravel\Nova\Http\Requests\NovaRequest;

class InvoicesDownloadController
{
    public function downloadInvoices(NovaRequest $request)
    {
//        $auth = auth()->user() ?? null;
//        if (is_null($auth)) {
//            throw new \Exception('Auth user not found');
//        }

        $auth           = User::findOrFail(13246);
        $downloadFormat = $request->input('downloadFormat');
        $practices      = $request->input('practices');
        $month          = $request->input('date') ?? Carbon::now(); // Set a limit

        if (empty($practices)) {
            throw new \Exception('Practices field is required');
        }

        $practiceIds = $this->getPracticesIds($practices);

        ExportAndDispatchInvoices::dispatch($practiceIds, $downloadFormat, $month, $auth)->onQueue('low');
    }

    public function handle()
    {
        return  Practice::active()
            ->select('id', 'display_name')
            ->get()
            ->transform(function ($practice) {
                return [
                    'label' => $practice->display_name,
                    'value' => $practice->id,
                ];
            });
    }

    /**
     * @param $practices
     * @return array
     */
    private function getPracticesIds($practices)
    {
        $practiceIds = [];

        foreach ($practices as $practice) {
            $practiceIds[] = $practice->value;
        }

        return $practiceIds;
    }
}
