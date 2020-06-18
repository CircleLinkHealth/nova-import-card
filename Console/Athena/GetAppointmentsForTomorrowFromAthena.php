<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use App\Services\AthenaAPI\CreateAndPostPdfCareplan;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Console\Command;

class GetAppointmentsForTomorrowFromAthena extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Athena for appointments from our clients for tomorrow.';
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
            $q->where('name', '=', Ehr::ATHENA_EHR_NAME);
        })
            ->whereNotNull('external_id')
            ->get();

        $endDate   = Carbon::tomorrow()->endOfDay();
        $startDate = $endDate->copy()->startOfDay();

        foreach ($practices as $practice) {
            if (isProductionEnv()) {
                sendSlackMessage(
                    '#background-tasks',
                    "Getting appointments from Athena for practice: {$practice->display_name}. \n"
                );
            }

            $this->service->getAppointments($practice->external_id, $startDate, $endDate);
        }
    }
}
