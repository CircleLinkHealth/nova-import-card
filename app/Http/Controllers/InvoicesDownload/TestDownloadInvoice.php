<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\InvoicesDownload;

use App\Http\Controllers\Controller;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;

class TestDownloadInvoice extends Controller
{
//    This will be a nova controller. Just developing here for now
    public function collectInvoicesFor()
    {
        $date      = now();
        $startDate = $date->copy()->startOfMonth()->toDateString();
        $endDate   = $date->copy()->endOfMonth()->toDateString();

        NurseInvoice::with([
            'nurseInfo' => function ($nurseInfo) use ($startDate, $endDate) {
                $nurseInfo->with(
                    [
                        'user.pageTimersAsProvider' => function ($pageTimer) use ($startDate, $endDate) {
                            $pageTimer->whereBetween('start_time', [$startDate, $endDate]);
                        },
                    ]
                );
            },
            //            Need nurses that are currently active or used to be for selected month
        ])->whereHas(
            'nurseInfo',
            function ($info) {
                $info->where('status', 'active')
                    ->when(isProductionEnv(), function ($info) {
                        $info->where('is_demo', false);
                    });
            }
        )->orWhereHas('nurseInfo.user.pageTimersAsProvider', function ($user) use ($startDate, $endDate) {
            $user->whereBetween('start_time', [$startDate, $endDate]);
        })
            ->chunk(20, function ($invoices) use ($startDate, $endDate) {
                // Collect invoices
            });
    }
}
