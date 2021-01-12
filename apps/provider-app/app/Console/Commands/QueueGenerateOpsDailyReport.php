<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\GenerateOpsDailyPracticeReport;
use App\Jobs\GenerateOpsDailyReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

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
            ? Carbon::parse($endDateStr)->setTime(23, 30)
            : Carbon::now();

        $practicesIds = Practice::whereHas('patients.patientInfo')
            ->activeBillable()
            ->pluck('id')
            ->toArray();

        $length      = count($practicesIds);
        $jobsToChain = [];

        for ($i = 0; $i < $length; ++$i) {
            $jobsToChain[] = new GenerateOpsDailyPracticeReport($practicesIds[$i], $endDate);
        }
        //add last job to gather all data - upload report to s3 - notify etc.
        $jobsToChain[] = new GenerateOpsDailyReport($practicesIds, $endDate);

        Bus::chain($jobsToChain)
            ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE))
            ->dispatch();
        $this->info("Ops Dashboard Jobs dispatched for date:{$endDate}");
    }
}
