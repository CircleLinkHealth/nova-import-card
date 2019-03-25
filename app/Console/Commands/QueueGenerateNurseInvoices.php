<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\GenerateNurseInvoice;
use CircleLinkHealth\Customer\Entities\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueGenerateNurseInvoices extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Nurse Invoice cached view for the current month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:nurseInvoices';

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
        GenerateNurseInvoice::dispatch(
            activeNurseNames()->keys()->all(),
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
            User::ofType('administrator')->pluck('id')->all(),
            true
        )->onQueue('high');
    }
}
