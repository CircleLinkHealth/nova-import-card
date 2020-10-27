<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\Eligibility\Entities\RpmProblem;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PatientProblemsForBillingProcessing
{
    protected int $patientId;
    
    protected User $patient;

    protected PatientServiceProcessorRepository $repo;

    public function __construct(int $patientId)
    {
        $this->patientId = $patientId;
    }

    public static function getArray(int $patientId): array
    {
        return (new static($patientId))
            ->setPatient()
            ->getProblems()
            ->toArray();
    }
    
    public static function getCollection(int $patientId): Collection
    {
        return (new static($patientId))
            ->setPatient()
            ->getProblems();
    }
    
    public static function getForCodes(int $patientId, array $codes): Collection
    {
        return self::getCollection($patientId)
            ->filter(fn(PatientProblemForProcessing $p) => count(array_intersect($codes, $p->getServiceCodes())) != 0);
    }
    
    private function setPatient():self
    {
        $this->patient = $this->repo()
            ->getPatientWithBillingDataForMonth($this->patientId, Carbon::now()->startOfMonth());
        
        return $this;
    }

    private function getProblems(): Collection
    {
        return $this->patient->ccdProblems->map(function (Problem $p){
            return (new PatientProblemForProcessing())
                ->setId($p->id)
                ->setCode($p->icd10Code())
                ->setServiceCodes($this->getServicesForProblem($p));
        })
            ->filter();
    }
    
    private function getServicesForProblem(Problem $problem) : array
    {
        if (Feature::isEnabled(BillingConstants::LOCATION_PROBLEM_SERVICES_FLAG)){
            return $problem->chargeableServiceCodesForLocation($this->patient->patientInfo->preferred_contact_location);
        }
        
        $services = [];
        
        if ($cpmProblem = $problem->cpmProblem)
        {
            $isDual = in_array($cpmProblem->name, CpmProblem::DUAL_CCM_BHI_CONDITIONS);
            
            if ($cpmProblem->is_behavioral || $isDual)
            {
                $services[] = ChargeableService::BHI;
            }
            
            if (! $cpmProblem->is_behavioral || $isDual)
            {
                $services[] = ChargeableService::CCM;
            }
        }
        
        $pcmProblems = $this->patient->primaryPractice->pcmProblems;
    
        if (! empty($pcmProblems)){
            $hasMatchingPcmProblem = $pcmProblems->filter(
                function (PcmProblem $pcmProblem) use ($problem) {
                    //todo: use string contains on name?
                    return $pcmProblem->code === $problem->icd10Code() || $pcmProblem->description === $problem->name;
                }
            )->isNotEmpty();
        
            if ($hasMatchingPcmProblem){
                $services[] = ChargeableService::PCM;
            }
        }
    
        $rpmProblems = $this->patient->primaryPractice->rpmProblems;
    
        if (! empty($rpmProblems)){
            $hasMatchingRpmProblem = $rpmProblems->filter(
                function (RpmProblem $rpmProblem) use ($problem) {
                    //todo: use string contains on name?
                    return $rpmProblem->code === $problem->icd10Code() || $rpmProblem->description === $problem->name;
                }
            )->isNotEmpty();
        
            if ($hasMatchingRpmProblem){
                $services[] = ChargeableService::RPM;
            }
        }
        
        return $services;
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }
}
