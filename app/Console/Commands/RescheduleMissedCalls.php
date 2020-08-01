<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Algorithms\Calls\ReschedulerHandler;
use Illuminate\Console\Command;

class RescheduleMissedCalls extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reschedule all past scheduled calls that were missed.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:reschedule';
    private $service;

    /**
     * Create a new command instance.
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
        $this->service->handle();

        $this->comment('Command ran.');
    }
}
