<?php


namespace CircleLinkHealth\Eligibility\Jobs\Athena;


use CircleLinkHealth\Core\Jobs\ChunksEloquentBuilderJobV2;
use CircleLinkHealth\Eligibility\Jobs\ProcessTargetPatientForEligibility;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Illuminate\Database\Eloquent\Builder;

class ProcessTargetPatientsForEligibilityInBatches extends ChunksEloquentBuilderJobV2
{
    protected int $practiceId;
    
    public function __construct(int $practiceId)
    {
        $this->practiceId = $practiceId;
    }
    
    public function query(): Builder
    {
        return TargetPatient::where('status', '=', TargetPatient::STATUS_TO_PROCESS)
                            ->where('practice_id', $this->practiceId)
                            ->with('batch');
    }
    
    public function handle() {
        $this->getBuilder()->eachById(function ($targetPatient) {
            ProcessTargetPatientForEligibility::dispatch($targetPatient);
        });
    }
}