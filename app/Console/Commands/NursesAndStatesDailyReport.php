<?php

namespace App\Console\Commands;

use App\SaasAccount;
use App\Services\NursesAndStatesDailyReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NursesAndStatesDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:nursesAndStatesDaily {forDate?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads data to S3';
    private $service;

    /**
     * Create a new command instance.
     *
     * @param NursesAndStatesDailyReportService $service
     */
    public function __construct(NursesAndStatesDailyReportService $service)
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
        $date = $this->argument('forDate');
        if ($date) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                die(1);
            }
        } else {
            $date = Carbon::today()->subDay(1)->startOfDay();
        }

        $data = $this->service->collectData($date);

        $fileName = "nurses-and-states-daily-report-{$date->toDateString()}.json";
        $path     = storage_path($fileName);
        $saved    = file_put_contents($path, json_encode($data));

        if ( ! $saved) {
            if (app()->environment('worker')) {
                sendSlackMessage(
                    '#callcenter_ops',
                    "Nurses weekly calls and work hours report {$date->toDateString()} could not be created. \n"
                );
            }
        }
        SaasAccount::whereSlug('circlelink-health')
                   ->first()
                   ->addMedia($path)
                   ->toMediaCollection($fileName);

        if (app()->environment('worker')) {
            sendSlackMessage(
                '#callcenter_ops',
                "Nurses weekly calls and work hours report {$date->toDateString()} created. \n"
            );
        }

        $this->info('Daily Nurses Calls & Work hrs uploaded to S3');
    }

}
