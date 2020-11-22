<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientIsOfServiceCode;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompareCurrentAndLegacyBillingDataPracticeChunk extends ChunksEloquentBuilderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $practiceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId)
    {
        $this->practiceId = $practiceId;
    }

    public function getBuilder(): Builder
    {
        return  User::ofType('participant')
            ->ofPractice($this->practiceId)
            ->whereHas('patientInfo', fn ($pi) => $pi->enrolled())
            ->whereHas('carePlan', fn ($cp) => $cp->whereIn('status', [
                CarePlan::QA_APPROVED,
                CarePlan::RN_APPROVED,
                CarePlan::PROVIDER_APPROVED,
            ]))
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
        $servicesToCompare = ChargeableService::cached()->whereNotIn('code', [
            ChargeableService::SOFTWARE_ONLY,
            ChargeableService::AWV_INITIAL,
            ChargeableService::AWV_SUBSEQUENT,
        ]);

        $this->getBuilder()->each(function ($patient) use ($servicesToCompare) {
            $toMatch = [];
            BillingCache::setBillingRevampIsEnabled(false);

            foreach ($servicesToCompare as $cs) {
                $toMatch['services'][$cs->code]['off'] = PatientIsOfServiceCode::execute($patient->id, $cs->code);
            }

            BillingCache::setBillingRevampIsEnabled(true);

            foreach ($servicesToCompare as $cs) {
                $toMatch['services'][$cs->code]['on'] = PatientIsOfServiceCode::execute($patient->id, $cs->code);
            }

            $mismatches = [];
            foreach ($toMatch['services'] as $code => $boolPerToggleArray) {
                if ($boolPerToggleArray['on'] !== $boolPerToggleArray['off']) {
                    $mismatches[] = $code;
                }
            }

            echo implode(',', $mismatches);
        });
    }
}
