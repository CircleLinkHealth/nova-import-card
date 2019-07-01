<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute;
use Illuminate\Database\Eloquent\Model;

class NurseInvoiceDailyDisputeTimeService
{
    /**
     * @param $input
     *
     * @return bool|Model|NurseInvoiceDailyDispute
     */
    public function storeDisputedTime($input)
    {
        $suggestedTime = NurseInvoiceDailyDispute::updateOrCreate(
            [
                'invoice_id'   => $input['invoiceId'],
                'disputed_day' => $input['disputedDay'],
            ],
            [
                'suggested_formatted_time' => $input['suggestedFormattedTime'],
                'disputed_formatted_time'  => $input['disputedFormattedTime'],
            ]
        );

        if ( ! $suggestedTime) {
            return false;
        }

        return $suggestedTime;
    }
}
