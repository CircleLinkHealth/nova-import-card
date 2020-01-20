<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\EligibilityCheck;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\Decorators\AddInsuranceFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\SharedModels\Entities\Ccda;
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
     */
    public function __construct(EligibilityCheckAdapter $adapter, TargetPatient $targetPatient, Ccda $ccda)
    {
        $this->adapter       = $adapter;
        $this->targetPatient = $targetPatient;
        $this->ccda          = $ccda;
    }

    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        $base = $this->adapter->adaptToEligibilityJob();

        return app(AddInsuranceFromAthenaToEligibilityJob::class)->addInsurancesFromAthena($base, $this->targetPatient, $this->ccda);
    }

    public function getInsuranceCollection(): ?Collection
    {
        return $this->insuranceCollection;
    }

    public function getMedicalRecord(): MedicalRecord
    {
        return $this->adapter->getMedicalRecord();
    }
}
