<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\EligibilityCheck;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\EligibilityJob;
use App\TargetPatient;
use CircleLinkHealth\Eligibility\Decorators\AddInsuranceFromAthenaToEligibilityJob;
use Illuminate\Support\Collection;

class AddInsurancesFromAthena implements EligibilityCheckAdapter
{
    /**
     * @var EligibilityCheckAdapter
     */
    protected $adapter;
    /**
     * @var TargetPatient
     */
    protected $targetPatient;
    /**
     * @var Collection
     */
    private $insuranceCollection;

    /**
     * AddInsurancesFromAthena constructor.
     *
     * @param EligibilityCheckAdapter $adapter
     * @param TargetPatient           $targetPatient
     */
    public function __construct(EligibilityCheckAdapter $adapter, TargetPatient $targetPatient)
    {
        $this->adapter       = $adapter;
        $this->targetPatient = $targetPatient;
    }

    /**
     * @throws \Exception
     *
     * @return EligibilityJob
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        $base = $this->adapter->adaptToEligibilityJob();

        return app(AddInsuranceFromAthenaToEligibilityJob::class)->addInsurancesFromAthena($base, $this->targetPatient, $this->targetPatient->ccda);
    }

    /**
     * @return Collection|null
     */
    public function getInsuranceCollection(): ?Collection
    {
        return $this->insuranceCollection;
    }

    /**
     * @return MedicalRecord
     */
    public function getMedicalRecord(): MedicalRecord
    {
        return $this->adapter->getMedicalRecord();
    }
}
