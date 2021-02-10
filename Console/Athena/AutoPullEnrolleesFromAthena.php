<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use Carbon\Carbon;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Jobs\Athena\ProcessTargetPatientsForEligibilityInBatches;
use CircleLinkHealth\Eligibility\Jobs\ChangeBatchStatus;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\Actions\DetermineEnrollmentEligibility;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class AutoPullEnrolleesFromAthena extends Command
{
    const MAX_DAYS_TO_PULL_AT_ONCE = 5;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull eligible patients from Athena API.';
    protected $options;
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:autoPullEnrolleesFromAthena {athenaPracticeId? : The Athena EHR practice id. `external_id` on table `practices`}
                                                                        {from? : From date yyyy-mm-dd}
                                                                        {to? : To date yyyy-mm-dd}
                                                                        {offset? : Offset results from athena api using number of target patients in the table}
                                                                        {batchId? : The Eligibility Batch Id}';

    /**
     * Create a new command instance.
     */
    public function __construct(ProcessEligibilityService $service)
    {
        parent::__construct();

        $this->service = $service;

        $this->options = [
            'filterProblems'      => true,
            'filterInsurance'     => false,
            'filterLastEncounter' => false,
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('athenaPracticeId')) {
            $practices = Practice::whereHas('ehr', function ($ehr) {
                $ehr->where('name', 'Athena');
            })
                ->where('external_id', $this->argument('athenaPracticeId'))
                ->get();
        } else {
            $practices = Practice::whereHas('ehr', function ($ehr) {
                $ehr->where('name', 'Athena');
            })
                ->whereHas('settings', function ($settings) {
                    $settings->where('api_auto_pull', 1);
                })
                ->get();
        }

        if (0 == $practices->count()) {
            if (isProductionEnv()) {
                sendSlackMessage(
                    '#parse_enroll_import',
                    "No Practices with checked 'api-auto-pull' setting were found for the weekly Athena Data Pull."
                );
            } else {
                return null;
            }
        }

        foreach ($practices as $practice) {
            Bus::dispatchChain($this->orchestrateEligibilityPull($practice))
               ->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
        }
    }

    /**
     * @throws \Illuminate\Auth\AuthenticationException
     * @return array                                    Array of Job objects
     */
    private function getAppointmentsJobs(Carbon $startDate, Carbon $endDate, int $athenaPracticeId, bool $offset, int $batchId): array
    {
        $service = app(DetermineEnrollmentEligibility::class);
        if ($startDate->diffInDays($endDate) > self::MAX_DAYS_TO_PULL_AT_ONCE) {
            $jobs        = [];
            $currentDate = $startDate->copy();
            do {
                $chunkStartDate = $currentDate->copy();
                $chunkEndDate   = $chunkStartDate->copy()->addDays(self::MAX_DAYS_TO_PULL_AT_ONCE);

                if ($chunkEndDate->isAfter($endDate)) {
                    $chunkEndDate = $endDate;
                }

                $jobs = array_merge($jobs, $service->getPatientIdFromAppointments($athenaPracticeId, $chunkStartDate, $chunkEndDate, $offset, $batchId));

                $currentDate = $chunkEndDate->copy()->addDay();
            } while ($currentDate->lt($endDate));

            return $jobs;
        }

        return $service->getPatientIdFromAppointments($athenaPracticeId, $startDate, $endDate, $offset, $batchId);
    }
    
    private function orchestrateEligibilityPull($practice)
    {
        $to   = Carbon::now()->format('y-m-d');
        $from = Carbon::now()->subMonth()->format('y-m-d');
    
        $offset = true;
    
        if ($this->argument('offset')) {
            $offset = $this->argument('offset');
        }
    
        if ($this->argument('from')) {
            $from = Carbon::createFromFormat('Y-m-d', $this->argument('from'));
        }
    
        if ($this->argument('to')) {
            $to = Carbon::createFromFormat('Y-m-d', $this->argument('to'));
        }
        
        $batch = null;
    
        if ($this->argument('batchId')) {
            $batch = EligibilityBatch::find($this->argument('batchId'));
        }
    
        if ( ! $batch) {
            $batch = $this->service->createBatch(EligibilityBatch::ATHENA_API, $practice->id, $this->options);
        }
    
        return array_merge(
            $this->getAppointmentsJobs(
                $from,
                $to,
                $practice->external_id,
                $offset,
                $batch->id,
            ),
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['not_started'])],
            (new ProcessTargetPatientsForEligibilityInBatches($batch->id))
                ->splitToBatches(),
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['complete'])],
        );
    }
}
