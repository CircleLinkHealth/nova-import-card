<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Jobs\ExportAndDispatchInvoices;
use Illuminate\Console\Command;

class ManualInvoiceDownloadCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export invoices for selected month (july), downolad format(pdf, csv) and send them in email to selected admin(userId)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manual:invoiceDownloadCommand {downloadFormat} {forMonth} {userIdToReceiveEmail}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $downloadFormat  = $this->argument('downloadFormat') ?? null;
        $month           = $this->argument('forMonth') ?? null;
        $toReceiveMailId = $this->argument('userIdToReceiveEmail') ?? null;

        $this->validateArgs($downloadFormat, $month, $toReceiveMailId);

        $monthToDate = Carbon::parse($month);

        $adminToReceiveMail = User::findOrFail($toReceiveMailId);

        ExportAndDispatchInvoices::dispatch($downloadFormat, $monthToDate, $adminToReceiveMail)->onQueue('low');
    }

    private function validateArgs(string $downloadFormat, string $month, int $adminToReceiveMail)
    {
        if (is_null($downloadFormat)) {
            $this->warn("Missing argument 'downloadFormat'");
        }

        if (is_null($month)) {
            $this->warn("Missing argument 'month'");
        }

        if (is_null($adminToReceiveMail)) {
            $this->warn("Missing argument 'adminToReceiveMail'");
        }
    }
}
