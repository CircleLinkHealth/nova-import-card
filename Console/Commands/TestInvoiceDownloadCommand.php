<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Jobs\CollectNursesWithInvoice;
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
        $downloadFormat = 'pdf'; // Or CSV.
        $practiceId     = 8; // Select Practices From Nova Invoices. Should be able to multi select
        $month          = Carbon::now();
        CollectNursesWithInvoice::dispatch($practiceId, $downloadFormat, $month)->onQueue('low');
    }
}
