<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\GenerateOpsDailyReport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueGenerateOpsDailyReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily operations report in json format, to be viewed in the admin OpsDashboard';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:OpsDailyReport {endDate? : End date in YYYY-MM-DD. The report will be produced up to 11pm of endDate.}';

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
        $endDateStr = $this->argument('endDate') ?? null;

        $endDate = $endDateStr
            ? Carbon::parse($endDateStr)->setTime(23, 0)
            : Carbon::now();

        GenerateOpsDailyReport::dispatch($endDate)->onQueue('high');
    }
}
