<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Observers;

use CircleLinkHealth\NurseInvoices\Jobs\CreateNurseInvoices;
use CircleLinkHealth\SharedModels\Entities\NurseInvoiceDailyDispute;

class NurseInvoiceDailyDisputeObserver
{
    /**
     * Handle the nurse invoice daily dispute "updated" event.
     */
    public function saved(NurseInvoiceDailyDispute $nurseInvoiceDailyDispute)
    {
        if ($nurseInvoiceDailyDispute->isDirty('status')) {
            CreateNurseInvoices::dispatch(
                $nurseInvoiceDailyDispute->disputed_day->copy()->startOfMonth(),
                $nurseInvoiceDailyDispute->disputed_day->copy()->endOfMonth(),
                [$nurseInvoiceDailyDispute->nurseInvoice->nurseInfo->user_id],
                false,
                null,
                true
            );
        }
    }
}