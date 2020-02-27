<?php

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Console\Command;

class FixAddInactiveProblemsToCommonwealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commonwealth:addmedicalhistory';
    
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
        
        TargetPatient::wherePracticeId(232)->with(['eligibilityJob', 'batch'])->chunk(
            500,
            function ($targetPatients) {
                $targetPatients->each(
                    function ($targetPatient) {
                        $data = $targetPatient->eligibilityJob->data;
                        if ( ! array_key_exists('medical_history', $data)) {
                            $data['medical_history'] = $this->api->getMedicalHistory(
                                $targetPatient->ehr_patient_id,
                                $targetPatient->ehr_practice_id,
                                $targetPatient->ehr_department_id
                            );
                        }
                        $problemNames = collect($data['medical_history']['questions'])->where(
                            'answer',
                            'Y'
                        )->pluck(
                            'question'
                        )->each(
                            function ($problemName) use (&$data) {
                                $data['problems'][] = [
                                    'name' => $problemName,
                                ];
                            }
                        );
                        $targetPatient->eligibilityJob->data = $data;
                        $targetPatient->eligibilityJob->save();
                        $targetPatient->eligibilityJob->process();
                    }
                );
            }
        );
    }
}
