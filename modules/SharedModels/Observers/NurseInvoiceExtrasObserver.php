<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Observers;

use CircleLinkHealth\NurseInvoices\Jobs\CreateNurseInvoices;
use CircleLinkHealth\SharedModels\Entities\NurseInvoiceExtra;

class NurseInvoiceExtrasObserver
{
    public function saved(NurseInvoiceExtra $nurseInvoiceExtra)
    {
        \Artisan::call('nurseinvoices:create', [
            'month'   => $nurseInvoiceExtra->date->copy()->startOfMonth()->toDateString(),
            'userIds' => $nurseInvoiceExtra->user_id,
        ]);
    }
}
