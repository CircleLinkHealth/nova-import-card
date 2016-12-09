<?php

namespace App\Console\Commands\Athena;

use App\ForeignId;
use App\Models\CCD\CcdVendor;
use App\Services\AthenaAPI\Service;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Maknz\Slack\Facades\Slack;

class GetAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getTodaysAppointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Athena for appointments from our clients for today.';

    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Service $athenaApi)
    {
        parent::__construct();

        $this->service = $athenaApi;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $vendors = CcdVendor::whereEhrName(ForeignId::ATHENA)->get();

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subMonth(2);

        foreach ($vendors as $vendor) {
            $this->service->getAppointments($vendor->practice_id, $startDate, $endDate);
        }

        if (app()->environment('worker')) {
            Slack::to('#background-tasks')
                ->send("Polled Athena for today's appointments. \n");
        }
    }
}
