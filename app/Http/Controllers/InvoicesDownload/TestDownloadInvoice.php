<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\InvoicesDownload;

use App\Http\Controllers\Controller;
use App\Jobs\NurseInvoiceDownload;
use Carbon\Carbon;

class TestDownloadInvoice extends Controller
{
//    This will be a in nova controller. Just developing here for now.
    public function collectData()
    {
        $downloadFormat = 'pdf'; // Or CSV.
        $practiceId     = [8]; // Select Practices From Nova Invoices
        $month          = Carbon::now();
        NurseInvoiceDownload::dispatch()->onQueue('low');
    }
}
