<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Jobs\CreateNurseInvoices;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Create extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect Nurses for last month to prepare invoices';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseinvoices:create';

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
        $startDate = Carbon::now()->subMonth(1)->startOfMonth();
        $endDate   = Carbon::now()->subMonth(1)->endOfMonth();

        CreateNurseInvoices::dispatch(
            $startDate,
            $endDate,
            $nurseUserIds = [],
            false,
            $requestedBy = null
        );
    }
}
