<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Console\Commands;

use CircleLinkHealth\CpmAdmin\Services\ApproveBillablePatientsService;
use Illuminate\Console\Command;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CountBillablePatientsForMonth extends Command
{
    /**
     * This command takes 4-7 seconds on my local machine, with xdebug enabled. Around 1000 patients (paginated).
     *
     * @var string
     */
    protected $description = 'Returns a count for the billable patients for a month';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count:billable-patients';

    /**
     * @var ApproveBillablePatientsService
     */
    private $service;

    /**
     * Create a new command instance.
     */
    public function __construct(ApproveBillablePatientsService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $start      = microtime(true);
        $practiceId = '8';
        $month      = now()->startOfMonth();

        /** @var Collection $result */
        $result = $this->service->getBillablePatientsForMonth($practiceId, $month);

        /** @var LengthAwarePaginator $summaries */
        $summaries = $result['summaries'];
        $count     = $summaries->total();

        json_encode($summaries);

        $time = round(microtime(true) - $start, 2);
        $this->info("$count patients! Time: {$time} seconds");
    }
}
