<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Eligibility\Contracts\EligibilityCheckAdapter;
use CircleLinkHealth\Eligibility\Decorators\InsuranceFromAthena;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use Illuminate\Support\Collection;

class AddInsurancesFromAthena implements EligibilityCheckAdapter
{
    /**
     * @var EligibilityCheckAdapter
     */
    protected $adapter;
    /**
     * @var Collection
     */
    private $insuranceCollection;

    /**
     * AddInsurancesFromAthena constructor.
     */
    public function __construct(EligibilityCheckAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        return app(InsuranceFromAthena::class)->decorate($this->adapter->adaptToEligibilityJob());
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
