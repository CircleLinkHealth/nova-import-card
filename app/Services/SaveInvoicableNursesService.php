<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\NurseInvoiceExtra;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;

class SaveInvoicableNursesService
{
    public function getInvoicableNurses()
    {
        $startDate = Carbon::today()->subMonth(1)->toDateString();
        $endDate   = Carbon::today()->toDateString();

        Nurse::with([
            'user',
            'summary' => function ($s) use ($startDate, $endDate) {
                $s->whereBetween('month_year', [
                    $startDate,
                    $endDate,
                ]);
            },
        ])->whereHas('summary', function ($s) use ($startDate, $endDate) {
            $s->whereBetween('month_year', [
                $startDate,
                $endDate,
            ]);
        })->where('status', 'active')
            ->chunk(10, function ($nurses) {
                 foreach ($nurses as $nurse) {
                     NurseInvoiceExtra::firstOrCreate(
                         [
                             'nurse_info_id' => $nurse->id,
                             'date'          => null,
                             'unit'          => null,
                             'value'         => null,
                         ]
                     );
                 }
             });

        return response()->json(['Nurse Invoices for last month are ready', 200]);
    }
}
