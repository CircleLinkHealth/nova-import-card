<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Eligibility\Contracts\EligibilityCheckAdapter;
use CircleLinkHealth\Eligibility\Decorators\AddCareTeamFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class AddCareTeamFromAthena implements EligibilityCheckAdapter
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
     * @var Ccda
     */
    protected $ccda;
    
    /**
     * AddInsurancesFromAthena constructor.
     *
     * @param EligibilityCheckAdapter $adapter
     * @param TargetPatient $targetPatient
     * @param Ccda $ccda
     */
    public function __construct(EligibilityCheckAdapter $adapter, TargetPatient $targetPatient, Ccda $ccda)
    {
        $this->adapter = $adapter;
        $this->targetPatient = $targetPatient;
        $this->ccda = $ccda;
    }
    
    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        return app(AddCareTeamFromAthenaToEligibilityJob::class)->addCareTeamFromAthena(
            $this->adapter->adaptToEligibilityJob(), $this->targetPatient, $this->ccda
        );
    }
    
    public function getMedicalRecord(): MedicalRecord
    {
        return $this->adapter->getMedicalRecord();
    }
}
