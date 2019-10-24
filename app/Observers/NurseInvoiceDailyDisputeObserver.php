<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Jobs\CreateNurseInvoices;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute;

class NurseInvoiceDailyDisputeObserver
{
    const STATUS_APPROVED = 'approved';

    /**
     * Handle the nurse invoice daily dispute "created" event.
     *
     * @param NurseInvoiceDailyDispute $nurseInvoiceDailyDispute
     */
    public function created(NurseInvoiceDailyDispute $nurseInvoiceDailyDispute)
    {
    }

    /**
     * Handle the nurse invoice daily dispute "deleted" event.
     *
     * @param NurseInvoiceDailyDispute $nurseInvoiceDailyDispute
     */
    public function deleted(NurseInvoiceDailyDispute $nurseInvoiceDailyDispute)
    {
    }

    /**
     * Handle the nurse invoice daily dispute "force deleted" event.
     *
     * @param NurseInvoiceDailyDispute $nurseInvoiceDailyDispute
     */
    public function forceDeleted(NurseInvoiceDailyDispute $nurseInvoiceDailyDispute)
    {
    }

    /**
     * Handle the nurse invoice daily dispute "restored" event.
     *
     * @param NurseInvoiceDailyDispute $nurseInvoiceDailyDispute
     */
    public function restored(NurseInvoiceDailyDispute $nurseInvoiceDailyDispute)
    {
    }

    /**
     * Handle the nurse invoice daily dispute "updated" event.
     *
     * @param NurseInvoiceDailyDispute $nurseInvoiceDailyDispute
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
