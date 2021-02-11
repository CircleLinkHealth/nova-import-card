<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummary;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Support\Collection;

class PatientServicesForTimeTracker
{
    private const NON_TIME_TRACKABLE_SERVICES = [
        'AWV1',
        'AWV2+',
    ];

    protected Carbon $month;

    protected int $patientId;

    protected PatientServiceProcessorRepository $repo;

    protected ?Collection $summaries;

    public function __construct(int $patientId, Carbon $month)
    {
        $this->patientId = $patientId;
        $this->month     = $month->startOfMonth();
    }

    public function get(): PatientChargeableSummaryCollection
    {
        return $this->setSummaries()
            ->consolidateSummaryData()
            ->createAndReturnResource();
    }

    public function getRaw(): Collection
    {
        return $this->setSummaries()
            ->consolidateSummaryData()
            ->summaries;
    }

    private function consolidateSummaryData(): self
    {
        if ($this->summaries->isEmpty()) {
            return $this;
        }

        return $this->filterUsingPatientServiceStatus()
            ->rejectNonTimeTrackerServices();
    }

    private function createAndReturnResource(): PatientChargeableSummaryCollection
    {
        return new PatientChargeableSummaryCollection(
            $this->summaries->transform(
                fn (ChargeablePatientMonthlyTime $summary) => new PatientChargeableSummary($summary)
            )
        );
    }

    private function createFauxSummariesFromLegacyData(): \Illuminate\Database\Eloquent\Collection
    {
        $summaries = new \Illuminate\Database\Eloquent\Collection();

        if ($this->patientEligibleForRHC()) {
            /** @var ChargeableService $rhc */
            $rhc = ChargeableService::cached()->firstWhere('code', ChargeableService::GENERAL_CARE_MANAGEMENT);

            $duration = Activity::wherePatientId($this->patientId)
                ->createdThisMonth('performed_at')
                ->where('chargeable_service_id', $rhc->id)
                ->sum('duration');

            $newSummary                        = new ChargeablePatientMonthlyTime();
            $newSummary->patient_user_id       = $this->patientId;
            $newSummary->chargeable_service_id = $rhc->id;
            $newSummary->total_time            = $duration;
            $newSummary->setRelation('chargeableService', $rhc);

            $summaries->push($newSummary);

            return $summaries;
        }

        $servicesDerivedFromPatientProblems = PatientProblemsForBillingProcessing::getCollection($this->patientId)
            ->transform(fn (PatientProblemForProcessing $p) => $p->getServiceCodes())
            ->flatten();

        $this->repo()
            ->getPatientWithBillingDataForMonth($this->patientId)
            ->forcedChargeableServices
            ->where('action_type', PatientForcedChargeableService::FORCE_ACTION_TYPE)
            ->each(fn ($s) => $servicesDerivedFromPatientProblems->push($s->chargeableService->code));

        $servicesDerivedFromPatientProblems->filter()->unique();

        if ($servicesDerivedFromPatientProblems->contains(ChargeableService::CCM)) {
            $servicesDerivedFromPatientProblems->push(...ChargeableService::CCM_PLUS_CODES);
        }

        if ($servicesDerivedFromPatientProblems->contains(ChargeableService::RPM)) {
            $servicesDerivedFromPatientProblems->push(...ChargeableService::RPM_PLUS_CODES);
        }

        $chargeableServices = ChargeableService::cached()
            ->whereIn('code', $servicesDerivedFromPatientProblems)
            ->collect();

        if ($chargeableServices->isEmpty()) {
            $duration = Activity::wherePatientId($this->patientId)
                ->createdThisMonth('performed_at')
                ->whereNull('chargeable_service_id')
                ->sum('duration');

            $newSummary                        = new ChargeablePatientMonthlyTime();
            $newSummary->patient_user_id       = $this->patientId;
            $newSummary->chargeable_service_id = -1;
            $newSummary->total_time            = $duration;
            $cs                                = new ChargeableService();
            $cs->id                            = -1;
            $cs->code                          = 'NONE';
            $cs->display_name                  = 'NONE';
            $newSummary->setRelation('chargeableService', $cs);

            $summaries->push($newSummary);

            return $summaries;
        }

        $activitiesForMonth = Activity::wherePatientId($this->patientId)
            ->createdThisMonth('performed_at')
            ->get();

        foreach ($chargeableServices as $service) {
            $newSummary                        = new ChargeablePatientMonthlyTime();
            $newSummary->patient_user_id       = $this->patientId;
            $newSummary->chargeable_service_id = $service->id;
            $newSummary->total_time            = $activitiesForMonth->where('chargeable_service_id', $service->id)->sum('duration');
            $newSummary->setRelation('chargeableService', $service);
            $summaries->push($newSummary);
        }

        return $summaries;
    }

    private function filterUsingPatientServiceStatus(): self
    {
        $this->summaries = $this->summaries
            ->filter(fn (ChargeablePatientMonthlySummary $summary) => -1 === $summary->chargeable_service_id ? true : PatientIsOfServiceCode::execute($summary->patient_user_id, $summary->chargeableService->code))
            ->values();

        return $this;
    }

    private function newBillingIsEnabled(): bool
    {
        return BillingCache::billingRevampIsEnabled();
    }

    private function patientEligibleForRHC(): bool
    {
        $patient = $this->repo()->getPatientWithBillingDataForMonth($this->patientId);

        return $patient->primaryPractice->chargeableServices->where('code', ChargeableService::GENERAL_CARE_MANAGEMENT)->count() > 0;
    }

    private function rejectNonTimeTrackerServices(): self
    {
        $this->summaries = $this->summaries
            ->reject(function (ChargeablePatientMonthlyTime $summary) {
                return in_array($summary->chargeable_service_name, self::NON_TIME_TRACKABLE_SERVICES);
            })
        ;

        return $this;
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
        $this->summaries = $this->newBillingIsEnabled() ?
            $this->repo()
                ->getChargeablePatientSummaries($this->patientId, $this->month)
                ->transform(fn ($entry) => $entry->replicate()) :
            $this->createFauxSummariesFromLegacyData();

        return $this;
    }
}
