<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\GenerateNurseDailyReportCsv;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class QueueGenerateNurseDailyReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a csv of nurse daily report.';
    /**
     * Command name
     * forDate: in the format of YYYY-MM-DD.
     *
     * @var string
     */
    protected $signature = 'report:nurseDaily {forDate?}';

    private static $generateForYesterdayThresholdMinutes = 10;

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
        $forDate = $this->argument('forDate');
        if ($forDate) {
            try {
                $forDate = Carbon::parse($forDate);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                die(1);
            }
        } else {
            //CPM-404
            //this job is executed at end of day to generate a daily report
            //sometimes the job gets delayed and runs after midnight,
            //which means its a new day, and the generated report will be empty
            $now                  = Carbon::now();
            $startOfDay           = $now->copy()->startOfDay();
            $minutesSinceMidnight = $now->diffInMinutes($startOfDay);
            if ($minutesSinceMidnight < static::$generateForYesterdayThresholdMinutes) {
                $forDate = Carbon::yesterday()->startOfDay();
            }
        }

        //not sure why we were passing a list of admin user ids
        //GenerateNurseDailyReportCsv::dispatch(User::ofType('administrator')->pluck('id'))
        GenerateNurseDailyReportCsv::dispatch($forDate)
            ->onQueue('high');
    }
}
