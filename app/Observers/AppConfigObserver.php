<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\AppConfig;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;

class AppConfigObserver
{
    public function saved(AppConfig $appConfig)
    {
        //Invalidate NurseInvoiceDisputeDeadline Cache if it has been edited.
        if (NurseInvoiceDisputeDeadline::NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_KEY == $appConfig->config_key) {
            \Cache::forget((new NurseInvoiceDisputeDeadline(Carbon::now()->subMonth()))->getCacheKey());
        }
    }
}
