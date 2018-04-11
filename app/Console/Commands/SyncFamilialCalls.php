<?php

namespace App\Console\Commands;

use App\Services\Calls\SchedulerService;
use Illuminate\Console\Command;

class SyncFamilialCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:syncFamilies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedules calls for patients who are in the same family and want to be called together.';
    private $schedulerService;

    /**
     * Create a new command instance.
     *
     * @param SchedulerService $schedulerService
     */
    public function __construct(SchedulerService $schedulerService)
    {
        parent::__construct();

        $this->schedulerService = $schedulerService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->schedulerService->syncFamilialCalls();
    }
}
