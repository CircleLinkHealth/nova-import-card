<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Carbon\Carbon;

class AttachDisputesToTimePerDay
{
    /**
     * @param $invoice
     *
     * @return array
     */
    public function getNurseDailyDisputes($invoice): array
    {
        $dailyDisputes = [];
        foreach ($invoice->dailyDisputes as $disputes) {
            $dailyDisputes[Carbon::parse($disputes->disputed_day)->copy()->toDateString()] = [
                'suggestedTime' => $disputes->suggested_formatted_time,
                'status'        => $disputes->status,
                'invalidated'   => $disputes->invalidated,
            ];
        }

        return $dailyDisputes;
    }

    /**
     * @param mixed $invoice
     *
     * @return mixed
     */
    public function putDisputesToTimePerDay($invoice)
    {
        $dailyDisputes = $this->getNurseDailyDisputes($invoice);

        $timePerDay = $invoice->invoice_data['timePerDay'];

        foreach ($dailyDisputes as $day => $disputes) {
            if (array_key_exists($day, $timePerDay)) {
                $timePerDay[$day]['suggestedTime'] = $disputes['suggestedTime'];
                $timePerDay[$day]['status']        = $disputes['status'];
                $timePerDay[$day]['invalidated']   = $disputes['invalidated'];
            }
        }

        $timePerDayWithDisputes                = $timePerDay;
        $invoiceDataWithDisputes               = $invoice->invoice_data;
        $invoiceDataWithDisputes['timePerDay'] = $timePerDayWithDisputes;

        return $invoiceDataWithDisputes;
    }
}
