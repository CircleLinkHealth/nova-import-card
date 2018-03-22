<?php

namespace App\Console\Commands;

use App\Services\OperationsDashboardService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OpsDashboardSlackDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opsDashboard:sendSlackDailyReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a Slack message containing counts of Enrolled/Withdrawn/Paused/G0506Hold for today';


    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OperationsDashboardService $service)
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
        $date     = Carbon::today();
        $fromDate = $date->copy()->startOfDay()->toDateTimeString();
        $toDate   = $date->copy()->endOfDay()->toDateTimeString();
        $dateType = 'day';

        $patients     = $this->service->getTotalPatients($fromDate, $toDate);
        $patientCount = $this->service->countPatientsByStatus($patients);

        if (app()->environment('worker')) {
            sendSlackMessage('#callcenter_ops',
                "Daily Report from Ops Dashboard \n Enrolled: $patientCount[enrolled] \n ");
        }


    }

}
