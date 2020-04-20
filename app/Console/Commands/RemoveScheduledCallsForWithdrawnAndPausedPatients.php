<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\Calls\SchedulerService;
use Illuminate\Console\Command;

class RemoveScheduledCallsForWithdrawnAndPausedPatients extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes scheduled calls for withdrawn and paused patients.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:removeWithdrawnAndPaused {patientUserIds?}';
    private $schedulerService;

    /**
     * Create a new command instance.
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
        $userIds = $this->argument('patientUserIds') ?? [];

        $result = $this->schedulerService->removeScheduledCallsForWithdrawnAndPausedPatients($userIds);

        $this->comment("${result} calls deleted.");
    }
}
