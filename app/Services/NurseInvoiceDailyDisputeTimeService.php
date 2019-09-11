<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute;
use Illuminate\Database\Eloquent\Model;

class NurseInvoiceDailyDisputeTimeService
{
    public function deleteDispute($invoiceId, $disputedDay)
    {
        NurseInvoiceDailyDispute::where([
            ['invoice_id', $invoiceId],
            ['disputed_day', $disputedDay],
        ])->delete();
    }

    /**
     * @param $input
     *
     * @return bool|Model|NurseInvoiceDailyDispute
     */
    public function saveDispute($input)
    {
        $suggestedFormattedTime = $input['suggestedFormattedTime'];
        $disputeSuggestedTime   = NurseInvoiceDailyDispute::updateOrCreate(
            [
                'invoice_id'   => $input['invoiceId'],
                'disputed_day' => $input['disputedDay'],
            ],
            [
                'suggested_formatted_time' => $suggestedFormattedTime,
                'disputed_formatted_time'  => $input['disputedFormattedTime'],
                'status'                   => $input['disputeStatus'],
            ]
        );

        if ( ! $disputeSuggestedTime) {
            return false;
        }

        return $disputeSuggestedTime;
    }
}
