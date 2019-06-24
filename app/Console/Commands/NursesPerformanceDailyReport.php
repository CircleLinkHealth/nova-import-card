<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\NursesPerformanceReportService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Console\Command;

class NursesPerformanceDailyReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads Nurses And States dashboard report to S3';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:nursesAndStatesDaily {forDate?}';
    private $service;

    /**
     * Create a new command instance.
     *
     * @param NursesPerformanceReportService $service
     */
    public function __construct(NursesPerformanceReportService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->argument('forDate') ?? null;

        if ($date) {
            $date = Carbon::parse($date);
        } else {
            $date = Carbon::yesterday()->startOfDay();
        }

        $this->warn('Running for '.$date->toDateTimeString());

        $data = $this->service->collectData($date);

        $fileName = "nurses-and-states-daily-report-{$date->toDateString()}.json";
        $path     = storage_path($fileName);
        $saved    = file_put_contents($path, json_encode($data));

        if ( ! $saved && isProductionEnv()) {
            sendSlackMessage(
                '#callcenter_ops',
                "Nurses And States dashboard report {$date->toDateString()} could not be created. \n"
            );

            $this->info('Nurses And States dashboard report could not be uploaded to S3');
        }

        SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($path)
            ->toMediaCollection($fileName);

        if (isProductionEnv()) {
            sendSlackMessage(
                '#callcenter_ops',
                "Nurses weekly calls and work hours report {$date->toDateString()} created. \n"
            );
        }

        $this->info('Daily Nurses Calls & Work hrs uploaded to S3');
    }
}
