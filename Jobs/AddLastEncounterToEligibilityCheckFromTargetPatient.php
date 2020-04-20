<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddLastEncounterToEligibilityCheckFromTargetPatient implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var int
     */
    protected $eligibilityJobId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $eligibilityJobId)
    {
        $this->eligibilityJobId = $eligibilityJobId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $e = EligibilityJob::with('targetPatient.ccda')->has('targetPatient.ccda')->findOrFail($this->eligibilityJobId);

        $encounters = collect($e->targetPatient->ccda->blueButtonJson()->encounters);

        $lastEncounter = $encounters->sortByDesc(function ($el) {
            return $el->date;
        })->first();

        if (is_object($lastEncounter) && property_exists($lastEncounter, 'date')) {
            $v = \Validator::make(['date' => $lastEncounter->date], ['date' => 'required|date']);

            if ($v->passes()) {
                $data                   = $e->data;
                $data['last_encounter'] = $lastEncounter->date;
                $e->data                = $data;
                $e->save();
            }
        }
    }
}
