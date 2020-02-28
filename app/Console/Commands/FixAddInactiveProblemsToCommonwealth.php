<?php

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Decorators\MedicalHistoryFromAthena;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Jobs\ProcessSinglePatientEligibility;
use Illuminate\Console\Command;

class FixAddInactiveProblemsToCommonwealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:addmedicalhistory {batchId}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add medical history from athena to commonwealth patients for reprocessing';
    /**
     * @var AthenaApiImplementation
     */
    protected $api;
    
    /**
     * Create a new command instance.
     *
     * @param AthenaApiImplementation $api
     */
    public function __construct(AthenaApiImplementation $api)
    {
        parent::__construct();
        $this->api = $api;
    }
    
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '2000M');
        
        TargetPatient::whereBatchId($this->argument('batchId'))->with(['eligibilityJob', 'batch'])->has(
            'eligibilityJob'
        )->whereDoesntHave(
            'eligibilityJob',
            function ($q) {
                $q->where('outcome', EligibilityJob::ELIGIBLE);
            }
        )->chunk(
            500,
            function ($targetPatients) {
                $targetPatients->each(
                    function ($targetPatient) {
                        $eligibilityJob = app(MedicalHistoryFromAthena::class)->decorate(
                            $targetPatient->eligibilityJob
                        );
                        if ($eligibilityJob->outcome !== EligibilityJob::ELIGIBLE) {
                            $eligibilityJob->status = EligibilityJob::STATUSES['not_started'];
                        }
                        ProcessSinglePatientEligibility::dispatch(
                            $targetPatient->eligibilityJob,
                            $targetPatient->eligibilityJob->batch,
                            $targetPatient->eligibilityJob->batch->practice
                        );
                    }
                );
            }
        );
    }
}
