<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\CalculateLoginLogoutActivityService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateLoginLogoutStats extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate time between login and logout events during the day';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:calculateLoginLogoutStats {forDate?}';
    /**
     * @var CalculateLoginLogoutStats
     */
    private $service;

    /**
     * Create a new command instance.
     *
     * @param CalculateLoginLogoutActivityService $service
     */
    public function __construct(CalculateLoginLogoutActivityService $service)
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
            $date = Carbon::yesterday()->toDateString();
        }
        $this->service->calculateLoginLogoutActivity($date);
        //@todo: report somewhere here
    }
}
