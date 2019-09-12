<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\CheckLogoutEventAndCreateStatsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckForMissingLogoutsAndInsert extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if user logout event was saved to DB and make stats to compare with users latest activity';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkForMissingLogoutsAndInsert {forDate?}';
    /**
     * @var CheckLogoutEventAndCreateStatsService
     */
    private $service;

    /**
     * Create a new command instance.
     *
     * @param CheckLogoutEventAndCreateStatsService $service
     */
    public function __construct(CheckLogoutEventAndCreateStatsService $service)
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
        $this->service->checkLogoutEvent($date);
    }
}
