<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Jobs\CreateNurseInvoices;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra;

class NurseInvoiceExtrasObserver
{
    public function saving(NurseInvoiceExtra $nurseInvoiceExtra)
    {
        CreateNurseInvoices::dispatch(
            $nurseInvoiceExtra->date->copy()->startOfMonth(),
            $nurseInvoiceExtra->date->copy()->endOfMonth(),
            [$nurseInvoiceExtra->user_id],
            false,
            null,
            true
        );
    }
}
