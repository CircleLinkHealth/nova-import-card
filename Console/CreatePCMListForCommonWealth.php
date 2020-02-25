<?php

namespace CircleLinkHealth\Eligibility\Console;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Jobs\ProcessCommonwealthPatientForPcm;
use Illuminate\Console\Command;

class CreatePCMListForCommonWealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pcm:common';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is the first time creating a PCM eligibility list for Commonwealth Pain Associates, PLLC';
    
    const PRACTICE_ID = 232;
  
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        EligibilityJob::whereHas(
            'batch',
            function ($q) {
                $q->where('practice_id', self::PRACTICE_ID);
            }
        )->with(['targetPatient.ccda'])->chunkById(
            100,
            function ($jobs) {
                $jobs->each(
                    function ($job) {
                        ProcessCommonwealthPatientForPcm::dispatch($this->resetPcm($job));
                    }
                );
            }
        );
    }
    
    /**
     * The structure I chose is difficult to query. Resetting it and introducing new one.
     *
     * @param EligibilityJob $eligibilityJob
     */
    private function resetPcm(EligibilityJob $eligibilityJob): EligibilityJob
    {
        $data = $eligibilityJob->data;
        
        if (array_key_exists('chargeable_services', $data)) {
            unset($data['chargeable_services']);
            $eligibilityJob->data = $data;
            $eligibilityJob->save();
        }
        
        return $eligibilityJob;
    }
}
