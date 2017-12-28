<?php

namespace App\Console\Commands;

use App\Services\Calls\SchedulerService;
use Illuminate\Console\Command;

class TuneScheduledCalls extends Command
{
    /**
 * The name and signature of the console command.
 *
 * @var string
 */
    protected $signature = 'calls:tune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tune scheduled calls according to updated ccm time.';
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
        return $this->schedulerService->tuneScheduledCallsWithUpdatedCCMTime();
    }
}
