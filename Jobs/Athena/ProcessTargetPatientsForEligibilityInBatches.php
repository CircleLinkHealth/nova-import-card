<?php


namespace CircleLinkHealth\Eligibility\Jobs\Athena;


use CircleLinkHealth\Core\Jobs\ChunksEloquentBuilderJobV2;
use CircleLinkHealth\Eligibility\Jobs\ProcessTargetPatientForEligibility;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Illuminate\Database\Eloquent\Builder;

class ProcessTargetPatientsForEligibilityInBatches extends ChunksEloquentBuilderJobV2
{
    protected int $batchId;
    
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }
    
    public function query(): Builder
    {
        return TargetPatient::where('status', '=', TargetPatient::STATUS_TO_PROCESS)
                            ->where('batch_id', $this->batchId)
                            ->select('id');
    }
    
    public function handle() {
        $this->getBuilder()->eachById(function ($targetPatient) {
            ProcessTargetPatientForEligibility::dispatch($targetPatient->id);
        });
    }
}