<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummary;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
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

    protected Collection $summaries;

    public function __construct(int $patientId, Carbon $month)
    {
        $this->patientId = $patientId;
        $this->month     = $month->startOfMonth();
    }

    public function get(): PatientChargeableSummaryCollection
    {
        return $this->setSummaries()
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

    private function groupSimilarCodes(Collection $summaries): Collection
    {
        /** @var ChargeablePatientMonthlySummaryView $ccmChargeableService */
        $ccmChargeableService = $summaries->filter(function (ChargeablePatientMonthlySummaryView $entry) {
            return ChargeableService::CCM === $entry->chargeable_service_code;
        })->first();

        /** @var ChargeablePatientMonthlySummaryView $rpmChargeableService */
        $rpmChargeableService = $summaries->filter(function (ChargeablePatientMonthlySummaryView $entry) {
            return ChargeableService::RPM === $entry->chargeable_service_code;
        })->first();

        $patientChargeableSummaries = collect();
        $summaries->each(function (ChargeablePatientMonthlySummaryView $entry) use ($patientChargeableSummaries, $ccmChargeableService, $rpmChargeableService) {
            $code = $entry->chargeable_service_code;
            if (in_array($code, [ChargeableService::CCM, ChargeableService::RPM])) {
                return;
            }
            if ($ccmChargeableService && in_array($code, [ChargeableService::CCM_PLUS_40, ChargeableService::CCM_PLUS_60])) {
                $ccmChargeableService->total_time += $entry->total_time;
            } elseif ($rpmChargeableService && ChargeableService::RPM40 === $code) {
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

        return $patientChargeableSummaries;
    }

    private function newBillingIsEnabled(): bool
    {
        return Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);
    }

    private function rejectNonTimeTrackerServices(Collection $summaries): Collection
    {
        return $summaries
            ->reject(function (ChargeablePatientMonthlySummaryView $summary) {
                return in_array($summary->chargeable_service_name, self::NON_TIME_TRACKABLE_SERVICES);
            })
            ->filter(fn ($summary) => PatientIsOfServiceCode::execute($summary->patient_user_id, $summary->chargeable_service_code));
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
            $summaries = $this->repo()->getChargeablePatientSummaries($this->patientId, $this->month);
        } else {
            $summaries = collect();
        }

        $summaries       = $this->rejectNonTimeTrackerServices($summaries);
        $this->summaries = $this->groupSimilarCodes($summaries);

        return $this;
    }
    
    private function createFauxSummariesFromLegacyData() : \Illuminate\Database\Eloquent\Collection
    {
        $servicesDerivedFromPatientProblems = PatientProblemsForBillingProcessing::getCollection($this->patientId)
            ->transform(fn(PatientProblemForProcessing $p) => $p->getServiceCodes())
            ->flatten()
            ->filter()
            ->unique();
        
        $chargeableServices = ChargeableService::whereIn('code', $servicesDerivedFromPatientProblems)->get();
        
        $activitiesForMonth = Activity::wherePatientId($this->patientId)
            ->createdThisMonth('performed_at')->get();
        
        $summaries = new \Illuminate\Database\Eloquent\Collection();
        foreach ($chargeableServices as $service){
            $summaries->push(ChargeablePatientMonthlySummaryView::make([
                'patient_user_id' => $this->patientId,
                'chargeable_service_id' => $service->id,
                'chargeable_service_code' => $service->code,
                'chargeable_service_name' => $service->display_name,
                'total_time'              => $activitiesForMonth->where('chargeable_service_id', $service->id)->sum('duration')
            ]));
        }
        
        return $summaries;
    }
}
