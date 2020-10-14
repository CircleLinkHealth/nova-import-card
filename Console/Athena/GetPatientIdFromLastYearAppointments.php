<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Psr\Log\InvalidArgumentException;

class GetPatientIdFromLastYearAppointments extends Command
{
    const MAX_DAYS_TO_PULL_AT_ONCE = 5;
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
    protected $signature = 'athena:getPatientIdFromLastYearAppointments {athenaPracticeId : The Athena EHR practice id. `external_id` on table `practices`}
                                                                        {from? : From date yyyy-mm-dd}
                                                                        {to? : To date yyyy-mm-dd}
                                                                        {offset? : Offset results from athena api using number of target patients in the table}
                                                                        {batchId? : The Eligibility Batch Id}';

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
        $athenaPracticeId = $this->argument('athenaPracticeId');

        if ( ! $athenaPracticeId) {
            return;
        }

        $endDate   = Carbon::today();
        $startDate = $endDate->copy()->subYear();
        $offset    = false;
        $batchId   = null;

        if ($this->argument('batchId')) {
            $batchId = $this->argument('batchId');
        }

        if ($this->argument('offset')) {
            $offset = (bool) $this->argument('offset');
        }

        if ($this->argument('from')) {
            $startDate = Carbon::parse($this->argument('from'));
        }

        if ($this->argument('to')) {
            $endDate = Carbon::parse($this->argument('to'));
        }

        if ($startDate->greaterThan($endDate)) {
            throw new InvalidArgumentException('Start date cannot be greater than end date.', 422);
        }

        if (isProductionEnv()) {
            sendSlackMessage(
                '#background-tasks',
                "Getting patient ids from the appointments from Athena, for practice_athena_id: ${athenaPracticeId}. \n"
            );
        }

        if ($startDate->diffInDays($endDate) > self::MAX_DAYS_TO_PULL_AT_ONCE) {
            $currentDate = $startDate->copy();
            do {
                $chunkStartDate = $currentDate->copy();
                $chunkEndDate   = $chunkStartDate->copy()->addDays(self::MAX_DAYS_TO_PULL_AT_ONCE);

                if ($chunkEndDate->isAfter($endDate)) {
                    $chunkEndDate = $endDate;
                }

                $this->line('Getting appointments for');
                $this->warn("Athena Practice Id: $athenaPracticeId");
                $this->line('for date range');
                $this->warn($chunkStartDate->toDateTimeString().' until '.$chunkEndDate->toDateTimeString());

                $this->service->getPatientIdFromAppointments($athenaPracticeId, $chunkStartDate, $chunkEndDate, $offset, $batchId);

                $currentDate = $chunkEndDate->copy()->addDay();
            } while ($currentDate->lt($endDate));
        }

        $this->service->getPatientIdFromAppointments($athenaPracticeId, $startDate, $endDate, $offset, $batchId);
    }
}
