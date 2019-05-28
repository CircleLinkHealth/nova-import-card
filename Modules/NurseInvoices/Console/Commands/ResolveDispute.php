<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\Dispute;
use Illuminate\Console\Command;

class ResolveDispute extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve Dispute Reminder';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseinvoice:resolveDispute';

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
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $disputes = Dispute::whereNull('resolved_at')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        if (0 !== $disputes) {
            //@todo: send to sara
        }
    }
}
