<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\SharedModels\Entities\NurseInvoiceDailyDispute;
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
                //we create a carbon object to validate hours and add leading 0 for uniform formatting
                'suggested_formatted_time' => \Carbon\Carbon::createFromFormat('H:i', $suggestedFormattedTime)->format('H:i'),
                'disputed_formatted_time'  => $input['disputedFormattedTime'],
                'status'                   => $input['disputeStatus'] ?? NurseInvoiceDailyDispute::STATUS_PENDING,
            ]
        );

        if ( ! $disputeSuggestedTime) {
            return false;
        }

        return $disputeSuggestedTime;
    }
}
