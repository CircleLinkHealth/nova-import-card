<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use CircleLinkHealth\Eligibility\Services\AthenaAPI\Actions\DetermineEnrollmentEligibility;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Console\Command;

class GetPatientIdFromAppointments extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve patient Ids from past booked appointments from the Athena API';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getPatientIdFromAppointments';

    private $service;

    /**
     * Create a new command instance.
     */
    public function __construct(DetermineEnrollmentEligibility $athenaApi)
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
        //getting all Practice Ids that belong to the Athena EHR
        $practices = Practice::whereHas('ehr', function ($q) {
            $q->where('name', '=', 'Athena');
        })
            ->whereNotNull('external_id')
            ->get();

        $endDate   = Carbon::today();
        $startDate = $endDate->copy()->subWeeks(2);

        //loop to getPatientIds from each practice found
        foreach ($practices as $practice) {
            if (isProductionEnv()) {
                sendSlackMessage(
                    '#background-tasks',
                    "Getting patient ids from the appointments from Athena, for practice: {$practice->display_name}. \n"
                );
            }

            $this->service->getPatientIdFromAppointments($practice->external_id, $startDate, $endDate);
        }
    }
}
