<?php

namespace App\Console\Commands;

use App\Services\Calls\SchedulerService;
use Illuminate\Console\Command;

class RemoveScheduledCallsForWithdrawnAndPausedPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:removeWithdrawnAndPaused';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes scheduled calls for withdrawn and paused patients.';
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
        return $this->schedulerService->removeScheduledCallsForWithdrawnAndPausedPatients();
    }
}
