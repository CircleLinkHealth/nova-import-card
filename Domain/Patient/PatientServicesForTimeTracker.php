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

        return $this->filterUsingPatientServiceStatus()
            ->rejectNonTimeTrackerServices()
            ->groupSimilarCodes();
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
        $servicesDerivedFromPatientProblems = PatientProblemsForBillingProcessing::getCollection($this->patientId)
            ->transform(fn (PatientProblemForProcessing $p) => $p->getServiceCodes())
            ->flatten()
            ->filter()
            ->unique();

        if ($servicesDerivedFromPatientProblems->contains(ChargeableService::CCM)) {
            $servicesDerivedFromPatientProblems->push(...ChargeableService::CCM_PLUS_CODES);
        }

        if ($servicesDerivedFromPatientProblems->contains(ChargeableService::RPM)) {
            $servicesDerivedFromPatientProblems->push(ChargeableService::RPM40);
        }

        $chargeableServices = ChargeableService::cached()
            ->whereIn('code', $servicesDerivedFromPatientProblems)
            ->collect();

        $activitiesForMonth = Activity::wherePatientId($this->patientId)
            ->createdThisMonth('performed_at')->get();

        $summaries = new \Illuminate\Database\Eloquent\Collection();
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
            ->filter(function ($summary){
                if (in_array($summary->chargeable_service_code, ChargeableService::ONLY_PLUS_CODES)){
                    return PatientIsOfServiceCode::excludeLocationCheck($summary->patient_user_id, $summary->chargeable_service_code);
                }
                return PatientIsOfServiceCode::execute($summary->patient_user_id, $summary->chargeable_service_code);
            });

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

        $this->summaries = $patientChargeableSummaries;

        return $this;
    }

    private function newBillingIsEnabled(): bool
    {
        return Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);
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
