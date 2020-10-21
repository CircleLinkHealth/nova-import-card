<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummary;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PatientServicesForTimeTracker
{
    protected Carbon $month;

    protected int $patientId;
    protected PatientServiceProcessorRepository $repo;

    protected EloquentCollection $summaries;

    public function __construct(int $patientId, Carbon $month)
    {
        $this->patientId = $patientId;
        $this->month     = $month;
    }

    public function get(): PatientChargeableSummaryCollection
    {
        return $this->setSummaries()
            ->groupSimilarCodes()
            ->createAndReturnResource();
    }

    private function createAndReturnResource(): PatientChargeableSummaryCollection
    {
        return new PatientChargeableSummaryCollection(
            $this->summaries->transform(
                fn (ChargeablePatientMonthlySummaryView $summary) => new PatientChargeableSummary($summary)
            )
        );
    }

    private function groupSimilarCodes(): self
    {
        //todo: implement

        return $this;
    }

    private function newBillingIsEnabled(): bool
    {
        //todo: add toggle
        return true;
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function setSummaries(): self
    {
        if ($this->newBillingIsEnabled()) {
            $this->summaries = $this->repo()->getChargeablePatientSummaries($this->patientId, $this->month);
        }else{
            //todo: get by looking at PCM problems, just like legacy billing.
            //e.g. CCM,and CCM plus = patient has 2 non bhi problems and practice has CCM
            //PCM patient has 1 pcm problem (pcm_problems table) and practice has PCM
            //BHI patient has at least one BHI Problem and practice has BHI
            //I will be using the PatientIsOfServiceCode action class. Inside, depending on toggle, will try to determine patient CS
            //until we make sure billing revamp works as intended
//            $this->summaries = $this->createFromPatientProblems();
        }

        return $this;
    }
}
