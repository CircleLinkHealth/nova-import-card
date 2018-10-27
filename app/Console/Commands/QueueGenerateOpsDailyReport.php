<?php

namespace App\Console\Commands;

use App\Jobs\GenerateOpsDailyReport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueGenerateOpsDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:OpsDailyReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily operations report in json format, to be viewed in the admin OpsDashboard';

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
        GenerateOpsDailyReport::dispatch()->onQueue('high');
    }
}
