<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Notifications\InvoiceReminder;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Console\Command;

class Reminder extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder to review invoices';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseinvoices:reminder';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $month = Carbon::now()->subMonth(1)->startOfMonth();

        NurseInvoice::with('nurse')
            ->where('month_year', $month)
            ->chunk(20, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $invoice->nurse->user->notify(new InvoiceReminder());
                }
            });
    }
}
