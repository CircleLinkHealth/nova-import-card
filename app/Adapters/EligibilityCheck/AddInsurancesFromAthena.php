<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\EligibilityCheck;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\CarePlanModels\Entities\Ccda;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Decorators\AddInsuranceFromAthenaToEligibilityJob;
use Illuminate\Support\Collection;

class AddInsurancesFromAthena implements EligibilityCheckAdapter
{
    /**
     * @var EligibilityCheckAdapter
     */
    protected $adapter;
    /**
     * @var Ccda
     */
    protected $ccda;
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
     * @param \CircleLinkHealth\CarePlanModels\Entities\Ccda                    $ccda
     */
    public function __construct(EligibilityCheckAdapter $adapter, TargetPatient $targetPatient, Ccda $ccda)
    {
        $this->adapter       = $adapter;
        $this->targetPatient = $targetPatient;
        $this->ccda          = $ccda;
    }

    /**
     * @throws \Exception
     *
     * @return \CircleLinkHealth\Eligibility\Entities\EligibilityJob
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        $base = $this->adapter->adaptToEligibilityJob();

        return app(AddInsuranceFromAthenaToEligibilityJob::class)->addInsurancesFromAthena($base, $this->targetPatient, $this->ccda);
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
