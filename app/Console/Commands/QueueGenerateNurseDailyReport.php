<?php

namespace App\Console\Commands;

use App\Jobs\GenerateNurseDailyReportCsv;
use App\User;
use Illuminate\Console\Command;

class QueueGenerateNurseDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:nurseDaily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genrates a csv of nurse daily report.';

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
            (new GenerateNurseDailyReportCsv(User::ofType('administrator')->pluck('id')))
                ->onQueue('reports')
        );
    }
}
