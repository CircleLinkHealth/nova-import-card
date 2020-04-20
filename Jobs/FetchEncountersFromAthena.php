<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Decorators\EncountersFromAthena;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchEncountersFromAthena implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var string|null
     */
    public $endDate;
    /**
     * @var string|null
     */
    public $startDate;
    /**
     * @var TargetPatient
     */
    public $targetPatient;

    /**
     * Create a new job instance.
     */
    public function __construct(TargetPatient $targetPatient, string $startDate = null, string $endDate = null)
    {
        $this->targetPatient = $targetPatient;
        $this->startDate     = $startDate;
        $this->endDate       = $endDate;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle(EncountersFromAthena $encountersFromAthena)
    {
        $this->targetPatient->loadMissing('eligibilityJob');

        $encountersFromAthena->setStartDate($this->startDate)->setEndDate($this->endDate)->decorate($this->targetPatient->eligibilityJob);
    }
}
