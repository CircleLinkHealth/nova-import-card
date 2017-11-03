<?php

namespace App\Console\Commands;

use App\Jobs\GenerateNurseInvoice;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueGenerateNurseInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:nurseInvoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Nurse Invoice cached view for the day before';

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
     * @return mixed
     */
    public function handle()
    {
        dispatch(
            (new GenerateNurseInvoice(activeNurseNames()->keys(),
                Carbon::yesterday()->startOfDay(),
                Carbon::yesterday()->endOfDay(),
                User::ofType('administrator')->pluck('id')
            ))->onQueue('reports')
        );
    }
}
