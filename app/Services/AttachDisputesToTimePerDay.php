<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\NurseInvoice;
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
     */
    public function getNurseDailyDisputes($invoice): array
    {
        $dailyDisputes = [];
        foreach ($invoice->dailyDisputes as $disputes) {
            $date = Carbon::parse($disputes->disputed_day)->copy()->toDateString();

            $dailyDisputes[$date] = [
                'suggestedTime'         => $disputes->suggested_formatted_time,
                'status'                => $disputes->status,
                'disputedFormattedTime' => $disputes->disputed_formatted_time,
                'invalidated'           => $this->checkForBonusesOnThisDate($invoice, $date),
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
    public function putDisputesToTimePerDay(NurseInvoice $invoice)
    {
        $dailyDisputes = $this->getNurseDailyDisputes($invoice);

        if ( ! is_array($invoice->invoice_data)) {
            $invoice->invoice_data = [];
        }

        $timePerDay = $invoice->invoice_data['timePerDay'] ?? [];

        foreach ($dailyDisputes as $day => $disputes) {
            $timePerDay[$day]['suggestedTime']         = $disputes['suggestedTime'] ?? null;
            $timePerDay[$day]['disputedFormattedTime'] = $disputes['disputedFormattedTime'] ?? null;
            $timePerDay[$day]['status']                = $disputes['status'] ?? null;
            $timePerDay[$day]['invalidated']           = $disputes['invalidated'] ?? null;
        }

        $timePerDayWithDisputes                = $timePerDay;
        $invoiceDataWithDisputes               = $invoice->invoice_data;
        $invoiceDataWithDisputes['timePerDay'] = $timePerDayWithDisputes;

        return $invoiceDataWithDisputes;
    }
}
