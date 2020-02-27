<?php

namespace App\Console\Commands;

use App\Traits\ValidatesDates;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Jobs\ProcessSinglePatientEligibility;
use Illuminate\Console\Command;

class FixAthenaBatchAddLastEncounter extends Command
{
    use ValidatesDates;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:add-last-encounter {batch_id} {start_date?} {end_date?}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch last encounter from Athena for each record in this batch.';
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
        
        TargetPatient::whereBatchId($this->argument('batch_id'))->with(['eligibilityJob', 'batch'])->has(
            'eligibilityJob'
        )->chunk(
            500,
            function ($targetPatients) {
                $targetPatients->each(
                    function ($targetPatient) {
                        $this->warn('Processing targetPatient:'.$targetPatient->id);
                        $this->processEncounters($targetPatient);
                    }
                );
            }
        );
        
        $this->line('batch finished');
    }
    
    /**
     * @param TargetPatient $targetPatient
     *
     * @throws \Exception
     */
    private function processEncounters(TargetPatient $targetPatient) {
        $data               = $targetPatient->eligibilityJob->data;
        $data['encounters'] = $this->api->getEncounters(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id,
            $this->hasArgument('start_date')
                ? $this->argument('start_date')
                : null,
            $this->hasArgument('end_date')
                ? $this->argument('end_date')
                : null
        );
    
        $lastEncounter = $this->carbon(
            collect($data['encounters']['encounters'])->sortByDesc(
                'appointmentstartdate'
            )->pluck('appointmentstartdate')->first()
        );
    
        if ($lastEncounter instanceof Carbon) {
            $data['last_encounter'] = $lastEncounter->toDateString();
            $targetPatient->eligibilityJob->last_encounter = $lastEncounter;
        }
    
        $targetPatient->eligibilityJob->data = $data;
    
        if ($targetPatient->eligibilityJob->isDirty()) {
            $targetPatient->eligibilityJob->save();
        }
    }
    
    private function carbon($lastEncounter)
    {
        if ($this->isValidDate($lastEncounter)) {
            return Carbon::createFromFormat(Carbon::ATOM, $lastEncounter);
        }
    }
}
