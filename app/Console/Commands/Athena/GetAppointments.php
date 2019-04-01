<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands\Athena;

use CircleLinkHealth\Customer\Entities\Practice;
use App\Services\AthenaAPI\CreateAndPostPdfCareplan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetAppointments extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Athena for appointments from our clients for today.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getAppointments';

    private $service;

    /**
     * Create a new command instance.
     */
    public function __construct(CreateAndPostPdfCareplan $athenaApi)
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
        $practices = Practice::whereHas('ehr', function ($q) {
            $q->where('name', '=', 'Athena');
        })
            ->whereNotNull('external_id')
            ->get();

        $endDate   = Carbon::today();
        $startDate = $endDate->copy()->subWeeks(2);

        foreach ($practices as $practice) {
            if (app()->environment('worker')) {
                sendSlackMessage(
                    '#background-tasks',
                    "Getting appointments from Athena for practice: {$practice->display_name}. \n"
                );
            }

            $this->service->getAppointments($practice->external_id, $startDate, $endDate);
        }
    }
}
