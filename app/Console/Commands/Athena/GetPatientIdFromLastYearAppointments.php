<?php

namespace App\Console\Commands\Athena;

use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
use App\TargetPatient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Psr\Log\InvalidArgumentException;

class GetPatientIdFromLastYearAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getPatientIdFromLastYearAppointments {athenaPracticeId : The Athena EHR practice id. `external_id` on table `practices`}
                                                                        {from? : From date yyyy-mm-dd}
                                                                        {to? : To date yyyy-mm-dd}';

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
        $athenaPracticeId = $this->argument('athenaPracticeId');

        if ( ! $athenaPracticeId) {
            return;
        }

        $endDate   = Carbon::today();
        $startDate = $endDate->copy()->subYear();

        if ($this->argument('from')) {
            $startDate = Carbon::parse($this->argument('from'));
        }

        if ($this->argument('to')) {
            $endDate = Carbon::parse($this->argument('to'));
        }

        if ($startDate->greaterThan($endDate)) {
            throw new InvalidArgumentException("Start date cannot be greater than end date.", 422);
        }

        if (app()->environment('worker')) {
            sendSlackMessage('#background-tasks',
                "Getting patient ids from the appointments from Athena, for practice_athena_id: $athenaPracticeId. \n");
        }

        $this->service->getPatientIdFromAppointments($athenaPracticeId, $startDate, $endDate, true);
    }
}
