<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MigrateAttestationDataChunk extends ChunksEloquentBuilderJob implements ShouldQueue
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
            $attestation->chargeable_month = Carbon::parse(($attestation->pms->month_year ?? $attestation->call->called_date))->startOfMonth()->startOfDay();
            $attestation->patient_user_id = $attestation->ccdProblem->patient_id ?? $attestation->pms->patient_id;

            if ($problem = $attestation->ccdProblem) {
                $attestation->ccd_problem_name = $problem->name;
                $attestation->ccd_problem_icd_10_code = $problem->icd10Code();
            }
            $attestation->save();
        });
    }
}
