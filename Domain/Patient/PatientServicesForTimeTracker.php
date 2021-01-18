<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
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

    private function consolidateSummaryData(): self
    {
        if ($this->summaries->isEmpty()) {
            return $this;
        }

        return $this->groupSimilarCodes()
            ->filterUsingPatientServiceStatus()
            ->rejectNonTimeTrackerServices();
    }

    private function createAndReturnResource(): PatientChargeableSummaryCollection
    {
        return new PatientChargeableSummaryCollection(
            $this->summaries->transform(
                fn (ChargeablePatientMonthlySummaryView $summary) => new PatientChargeableSummary($summary)
            )
        );
    }

    private function createFauxSummariesFromLegacyData(): \Illuminate\Database\Eloquent\Collection
    {
        $summaries = new \Illuminate\Database\Eloquent\Collection();

        if ($this->patientEligibleForRHC()) {
            $rhc = ChargeableService::cached()->firstWhere('code', ChargeableService::GENERAL_CARE_MANAGEMENT);

            $duration = Activity::wherePatientId($this->patientId)
                ->createdThisMonth('performed_at')
                ->where('chargeable_service_id', $rhc->id)
                ->sum('duration');

            $newSummary                          = new ChargeablePatientMonthlySummaryView();
            $newSummary->patient_user_id         = $this->patientId;
            $newSummary->chargeable_service_id   = $rhc->id;
            $newSummary->chargeable_service_code = $rhc->code;
            $newSummary->chargeable_service_name = $rhc->display_name;
            $newSummary->total_time              = $duration;

            $summaries->push($newSummary);

            return $summaries;
        }

        $servicesDerivedFromPatientProblems = PatientProblemsForBillingProcessing::getCollection($this->patientId)
            ->transform(fn (PatientProblemForProcessing $p) => $p->getServiceCodes())
            ->flatten();

        $this->repo()
            ->getPatientWithBillingDataForMonth($this->patientId)
            ->forcedChargeableServices
            ->where('is_forced', true)
            ->each(fn ($s) => $servicesDerivedFromPatientProblems->push($s->code));

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

            $newSummary                          = new ChargeablePatientMonthlySummaryView();
            $newSummary->patient_user_id         = $this->patientId;
            $newSummary->chargeable_service_id   = -1;
            $newSummary->chargeable_service_code = 'NONE';
            $newSummary->chargeable_service_name = 'NONE';
            $newSummary->total_time              = $duration;

            $summaries->push($newSummary);

            return $summaries;
        }

        $activitiesForMonth = Activity::wherePatientId($this->patientId)
            ->createdThisMonth('performed_at')
            ->get();

        foreach ($chargeableServices as $service) {
            $newSummary                          = new ChargeablePatientMonthlySummaryView();
            $newSummary->patient_user_id         = $this->patientId;
            $newSummary->chargeable_service_id   = $service->id;
            $newSummary->chargeable_service_code = $service->code;
            $newSummary->chargeable_service_name = $service->display_name;
            $newSummary->total_time              = $activitiesForMonth->where('chargeable_service_id', $service->id)->sum('duration');
            $summaries->push($newSummary);
        }

        return $summaries;
    }

    private function filterUsingPatientServiceStatus(): self
    {
        $this->summaries = $this->summaries
            ->filter(fn ($summary) => -1 === $summary->chargeable_service_id ? true : PatientIsOfServiceCode::execute($summary->patient_user_id, $summary->chargeable_service_code))
            ->values();

        return $this;
    }

    private function groupSimilarCodes(): self
    {
        /** @var ChargeablePatientMonthlySummaryView $ccmChargeableService */
        $ccmChargeableService = $this->summaries->filter(fn (ChargeablePatientMonthlySummaryView $entry) => ChargeableService::CCM === $entry->chargeable_service_code)
            ->first();

        /** @var ChargeablePatientMonthlySummaryView $rpmChargeableService */
        $rpmChargeableService = $this->summaries->filter(fn (ChargeablePatientMonthlySummaryView $entry) => ChargeableService::RPM === $entry->chargeable_service_code)
            ->first();

        $patientChargeableSummaries = collect();
        $this->summaries
            ->each(function (ChargeablePatientMonthlySummaryView $entry) use ($patientChargeableSummaries, $ccmChargeableService, $rpmChargeableService) {
                $code = $entry->chargeable_service_code;
                if (in_array($code, [ChargeableService::CCM, ChargeableService::RPM])) {
                    return;
                }
                if ($ccmChargeableService && in_array($code, ChargeableService::CCM_PLUS_CODES)) {
                    $ccmChargeableService->total_time += $entry->total_time;
                } elseif ($rpmChargeableService && in_array($code, ChargeableService::RPM_PLUS_CODES)) {
                    $rpmChargeableService->total_time += $entry->total_time;
                } else {
                    $patientChargeableSummaries->push($entry);
                }
            });

        if ($ccmChargeableService) {
            $patientChargeableSummaries->push($ccmChargeableService);
        }
        if ($rpmChargeableService) {
            $patientChargeableSummaries->push($rpmChargeableService);
        }

        $this->summaries = $patientChargeableSummaries;

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
            ->reject(function (ChargeablePatientMonthlySummaryView $summary) {
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
                //create copies of the models because we are modifying them in groupSimilarCodes()
                ->transform(fn ($entry) => $entry->replicate()) :
            $this->createFauxSummariesFromLegacyData();

        return $this;
    }
}
