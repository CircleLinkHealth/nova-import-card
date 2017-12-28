<?php

namespace App\Console\Commands;

use App\Algorithms\Calls\ReschedulerHandler;
use Illuminate\Console\Command;

class RescheduleMissedCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:reschedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reschedule all past scheduled calls that were missed.';
    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ReschedulerHandler $service)
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
        $handled = $this->service->handle();

        if ( ! empty($handled)) {
            $message = "The CPMbot just rescheduled some calls.\n";

            foreach ($handled as $call) {
                $message = "We just fixed call: {$call->id}. \n";
            }

            sendSlackMessage('#background-tasks', $message);
        }
    }
}
