<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Jobs\CreateNurseInvoices;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoicesForNonDemoNurses extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for nurses who worked last month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseinvoices:create {month? : Month to generate the invoice for in YYYY-MM format. Defaults to previous month.} {userIds?* : Space separated. Leave empty to send to all}';

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
        $month = $this->argument('month') ?? null;

        if ($month) {
            $month = Carbon::createFromFormat('Y-m', $month);
        } else {
            $month = Carbon::now()->subMonth();
        }

        $start = $month->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $userIds = (array) $this->argument('userIds') ?? [];

        CreateNurseInvoices::dispatch(
            $start,
            $end,
            $userIds,
            false,
            $requestedBy = null
        );

        $this->info('Command dispatched!');

        $forNurses = empty($userIds)
            ? 'all nurses.'
            : 'nurses with user IDs '.implode(', ', $userIds);

        $this->info(
            "Will create invoices for {$month->format('Y-m')}, for $forNurses."
        );
    }
}
