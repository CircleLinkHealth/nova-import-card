<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MigrateAttestationDataChunk extends ChunksEloquentBuilderJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getBuilder(): Builder
    {
        return AttestedProblem::with([
            'ccdProblem' => function ($p) {
                $p->with([
                    'cpmProblem',
                    'icd10Codes',
                ]);
            },
            'patient',
            'pms',
            'call',
        ])
            ->offset($this->getOffset())
            ->limit($this->getLimit());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getBuilder()->each(function (AttestedProblem $attestation) {
            $attestedMonth = optional($attestation->pms)->month_year ?? optional($attestation->call)->called_date ?? $attestation->created_at ?? $attestation->updated_at;
            if ( ! is_null($attestedMonth)) {
                $attestation->chargeable_month = Carbon::parse($attestedMonth)->startOfMonth()->startOfDay();
            }

            $attestation->patient_user_id = optional($attestation->ccdProblem)->patient_id ?? optional($attestation->pms)->patient_id;

            if ($problem = $attestation->ccdProblem) {
                $attestation->ccd_problem_name = $problem->name;
                $attestation->ccd_problem_icd_10_code = $problem->icd10Code();
            }
            $attestation->save();
        });
    }
}
