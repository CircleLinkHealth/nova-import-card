<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;
use Illuminate\Console\Command;

class TestInvoiceDownloadCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:invoiceDownloadCommand';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $auth           = User::findOrFail(13246);
        $downloadFormat = NurseInvoice::PDF_DOWNLOAD_FORMAT; // CSV or PDF.
        $practiceIds    = [8, 24];
        $month          = Carbon::now(); // Set a limit
        ExportAndDispatchInvoices::dispatch($practiceIds, $downloadFormat, $month, $auth)->onQueue('low');
    }
}
