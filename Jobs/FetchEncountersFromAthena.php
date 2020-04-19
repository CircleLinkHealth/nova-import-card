<?php

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Decorators\EncountersFromAthena;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchEncountersFromAthena implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var TargetPatient
     */
    public $targetPatient;
    /**
     * @var string|null
     */
    public $startDate;
    /**
     * @var string|null
     */
    public $endDate;
    
    /**
     * Create a new job instance.
     *
     * @param TargetPatient $targetPatient
     * @param string|null $startDate
     * @param string|null $endDate
     */
    public function __construct(TargetPatient $targetPatient, string $startDate = null, string $endDate = null)
    {
        $this->targetPatient = $targetPatient;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    /**
     * Execute the job.
     *
     * @param EncountersFromAthena $encountersFromAthena
     *
     * @return void
     * @throws \Exception
     */
    public function handle(EncountersFromAthena $encountersFromAthena)
    {
        $this->targetPatient->loadMissing('eligibilityJob');
        
        $encountersFromAthena->setStartDate($this->startDate)->setEndDate($this->endDate)->decorate($this->targetPatient->eligibilityJob);
    }
}
