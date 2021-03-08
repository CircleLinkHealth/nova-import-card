<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Eligibility\Contracts\EligibilityCheckAdapter;
use CircleLinkHealth\Eligibility\Decorators\CareTeamFromAthena;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;

class AddCareTeamFromAthena implements EligibilityCheckAdapter
{
    /**
     * @var EligibilityCheckAdapter
     */
    protected $adapter;

    /**
     * AddInsurancesFromAthena constructor.
     *
     * @param TargetPatient $targetPatient
     * @param Ccda          $ccda
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
        return app(CareTeamFromAthena::class)->decorate(
            $this->adapter->adaptToEligibilityJob()
        );
    }

    public function getMedicalRecord(): MedicalRecord
    {
        return $this->adapter->getMedicalRecord();
    }
}
