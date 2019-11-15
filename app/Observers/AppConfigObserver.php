<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\AppConfig;
use Carbon\Carbon;
use CircleLinkHealth\Customer\PracticesRequiringBhiConsent;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;

class AppConfigObserver
{
    public function saved(AppConfig $appConfig)
    {
        //Invalidate NurseInvoiceDisputeDeadline Cache if it has been edited.
        if (NurseInvoiceDisputeDeadline::NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_KEY == $appConfig->config_key) {
            \Cache::forget((new NurseInvoiceDisputeDeadline(Carbon::now()->subMonth()))->getCacheKey());
        }

        if (PracticesRequiringBhiConsent::PRACTICE_REQUIRES_BHI_CONSENT_NOVA_KEY == $appConfig->config_key) {
            \Cache::forget(PracticesRequiringBhiConsent::PRACTICE_REQUIRES_BHI_CONSENT_NOVA_KEY);
        }
    }
}
