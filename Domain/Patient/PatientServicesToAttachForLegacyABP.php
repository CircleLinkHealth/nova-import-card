<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class PatientServicesToAttachForLegacyABP
{
    protected Collection $activities;

    protected array $eligibleServices = [];

    protected User $patient;

    protected int $patientId;

    protected array $practiceServices;

    protected PatientServiceProcessorRepository $repo;

    protected PatientMonthlySummary $summary;

    public function __construct(PatientMonthlySummary $summary, array $practiceServices = [])
    {
        $this->summary          = $summary;
        $this->patientId        = $summary->patient_id;
        $this->practiceServices = $practiceServices;
    }

    public function get(): array
    {
        return $this->setPatient()
            ->setEligibleServices()
            ->setActivities()
            ->returnFulfilledServicesWithTime();
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function returnFulfilledServicesWithTime()
    {
        return [];
    }

    private function setActivities(): self
    {
        //set activities and group by chargeable service id. pluck and return?

        return $this;
    }

    private function setEligibleServices(): self
    {
        if (in_array(ChargeableService::GENERAL_CARE_MANAGEMENT, $this->practiceServices)) {
            $this->eligibleServices[] = ChargeableService::GENERAL_CARE_MANAGEMENT;

            return $this;
        }

        $servicesDerivedFromPatientProblems = PatientProblemsForBillingProcessing::getCollection($this->patientId)
            ->transform(fn (PatientProblemForProcessing $p) => $p->getServiceCodes())
            ->flatten()
            ->filter()
            ->unique();

        foreach ($servicesDerivedFromPatientProblems as $service) {
            if (PatientIsOfServiceCode::execute($this->patientId, $service)) {
                $this->eligibleServices[] = $service;
            }

            if (ChargeableService::RPM === $service) {
                $this->eligibleServices[] = ChargeableService::RPM40;
            }

            if (ChargeableService::CCM === $service) {
                $this->eligibleServices = array_merge($this->eligibleServices, ChargeableService::CCM_PLUS_CODES);
            }
        }

        return $this;
    }

    private function setPatient(): self
    {
        if ( ! isset($this->patient)) {
            $this->patient = $this->repo()->getPatientWithBillingDataForMonth($this->patientId, $this->month);
        }

        return $this;
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
