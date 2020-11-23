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

class PatientProblemsForBillingProcessing
{
    const SERVICE_PROBLEMS_MIN_COUNT_MAP = [
        ChargeableService::GENERAL_CARE_MANAGEMENT => 2,
        ChargeableService::CCM                     => 2,
        ChargeableService::BHI                     => 1,
        ChargeableService::PCM                     => 1,
        ChargeableService::RPM                     => 1,
    ];
    protected ?User $patient;
    protected int $patientId;

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
            ->filter(fn (PatientProblemForProcessing $p) => 0 != count(array_intersect($codes, $p->getServiceCodes())));
    }

    private function getProblems(): Collection
    {
        if (is_null($this->patient)) {
            return collect();
        }

        return $this->patient->ccdProblems->map(function (Problem $p) {
            return (new PatientProblemForProcessing())
                ->setId($p->id)
                ->setCode($p->icd10Code())
                ->setServiceCodes($this->getServicesForProblem($p));
        })
            ->filter();
    }

    private function getServicesForProblem(Problem $problem): array
    {
        if (Feature::isEnabled(BillingConstants::LOCATION_PROBLEM_SERVICES_FLAG)) {
            return $problem->chargeableServiceCodesForLocation($this->patient->patientInfo->preferred_contact_location);
        }

        $primaryPractice = $this->patient->primaryPractice;

        if (is_null($primaryPractice)) {
            sendSlackMessage('#billing_alerts', "Warning! (PatientProblemsForBillingProcessing:) Patient ({$this->patient->id}) does not have a primary practice.");

            return [];
        }

        $services = [];
        //todo: clear logic with a clearer mind
        $practiceHasBhi    = ! is_null($primaryPractice->chargeableServices->firstWhere('code', ChargeableService::BHI));
        $practiceHasRhc    = ! is_null($primaryPractice->chargeableServices->firstWhere('code', ChargeableService::GENERAL_CARE_MANAGEMENT));
        $bhiProblemsAreCcm = ! $practiceHasBhi || $practiceHasRhc;

        if ($cpmProblem = $problem->cpmProblem) {
            $isDual = in_array($cpmProblem->name, CpmProblem::DUAL_CCM_BHI_CONDITIONS);

            if ( ! $bhiProblemsAreCcm && ($cpmProblem->is_behavioral || $isDual)) {
                $services[] = ChargeableService::BHI;
            }

            if ($bhiProblemsAreCcm || ! $cpmProblem->is_behavioral || $isDual) {
                $services[] = ChargeableService::CCM;
            }
        }

        if ($practiceHasRhc) {
            if (is_null($cpmProblem) && empty($services)) {
                $services[] = ChargeableService::CCM;
            }

            return $services;
        }
        $pcmProblems = $primaryPractice->pcmProblems;

        if ( ! empty($pcmProblems)) {
            $hasMatchingPcmProblem = $pcmProblems->filter(
                function (PcmProblem $pcmProblem) use ($problem) {
                    return $this->sanitize($pcmProblem->code) === $this->sanitize($problem->icd10Code())
                        || $this->sanitize($pcmProblem->description) === $this->sanitize($problem->name)
                        || $this->sanitize($pcmProblem->description) === $this->sanitize($problem->original_name);
                }
            )->isNotEmpty();

            if ($hasMatchingPcmProblem) {
                $services[] = ChargeableService::PCM;
            }
        }

        $rpmProblems = $primaryPractice->rpmProblems;

        if ( ! empty($rpmProblems)) {
            $hasMatchingRpmProblem = $rpmProblems->filter(
                function (RpmProblem $rpmProblem) use ($problem) {
                    //todo: use string contains on name?
                    return $rpmProblem->code === $problem->icd10Code() || $rpmProblem->description === $problem->name;
                }
            )->isNotEmpty();

            if ($hasMatchingRpmProblem) {
                $services[] = ChargeableService::RPM;
            }
        }

        if (is_null($cpmProblem) && empty($services)) {
            $services[] = ChargeableService::CCM;
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

    private function sanitize(string $string): string
    {
        return trim(strtolower($string));
    }

    private function setPatient(): self
    {
        $this->patient = $this->repo()
            ->getPatientWithBillingDataForMonth($this->patientId, Carbon::now()->startOfMonth());

        return $this;
    }
}
