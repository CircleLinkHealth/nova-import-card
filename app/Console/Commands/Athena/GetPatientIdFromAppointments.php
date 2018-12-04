<?php

namespace App\Console\Commands\Athena;

use App\Practice;
use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetPatientIdFromAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getPatientIdFromAppointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve patient Ids from past booked appointments from the Athena API';


    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
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

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subWeeks(2);

        //loop to getPatientIds from each practice found
        foreach ($practices as $practice) {
            if (app()->environment('worker')) {
                sendSlackMessage(
                    '#background-tasks',
                    "Getting patient ids from the appointments from Athena, for practice: {$practice->display_name}. \n"
                );
            }

            $this->service->getPatientIdFromAppointments($practice->external_id, $startDate, $endDate);
        }
    }
}
