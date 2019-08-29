<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AttachDisputesToTimePerDay
{
    /**
     * @param $invoice
     * @param $date
     *
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    public function checkForBonusesOnThisDate($invoice, $date)
    {
        $userId = $this->getUserId($invoice);

        $bonusDates = $this->getBonusMatchedDate($userId, $date);

        $invalidated = $bonusDates->map(function ($q) use ($date) {
            return $q->where('date', $date)->exists();
        })->first();

        if ( ! $invalidated) {
            $invalidated = false;
        }

        return $invalidated;
    }

    /**
     * @param $userId
     * @param $date
     *
     * @return Builder[]|Collection
     */
    public function getBonusMatchedDate($userId, $date)
    {
        return NurseInvoiceExtra::where([
            ['user_id', '=', $userId],
            ['date', '=', $date],
        ])->select('date')->get();
    }

    /**
     * @param $invoice
     *
     * @return array
     */
    public function getNurseDailyDisputes($invoice): array
    {
        $dailyDisputes = [];
        foreach ($invoice->dailyDisputes as $disputes) {
            $date = Carbon::parse($disputes->disputed_day)->copy()->toDateString();

            $dailyDisputes[$date] = [
                'suggestedTime' => $disputes->suggested_formatted_time,
                'status'        => $disputes->status,
                'invalidated'   => $this->checkForBonusesOnThisDate($invoice, $date),
            ];
        }

        return $dailyDisputes;
    }

    /**
     * @param $invoice
     *
     * @return mixed
     */
    public function getUserId($invoice)
    {
        return $invoice->nurse->user_id;
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
