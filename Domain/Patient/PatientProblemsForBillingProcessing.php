<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\SharedModels\Entities\Problem;

class PatientProblemsForBillingProcessing
{
    protected int $patientId;

    protected PatientServiceProcessorRepository $repo;

    public function __construct(int $patientId)
    {
        $this->patientId = $patientId;
    }

    public static function get(int $patientId): array
    {
        return (new static($patientId))->getProblemsDTO();
    }

    private function getProblemsDTO(): array
    {
        $patient = $this->repo()
            ->getPatientWithBillingDataForMonth($this->patientId, Carbon::now()->startOfMonth());

        return $patient->ccdProblems->map(function (Problem $p) use ($patient) {
            return (new PatientProblemForProcessing())
                ->setId($p->id)
                ->setCode($p->icd10Code())
                ->setServiceCodes($p->chargeableServiceCodesForLocation($patient->patientInfo->preferred_contact_location));
        })
            ->filter()
            ->toArray();
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }
}
