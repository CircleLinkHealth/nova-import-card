<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Factories;

use CircleLinkHealth\Eligibility\Exceptions\CcdaWasNotFetchedFromAthenaApi;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Checkables\AthenaCheckable;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\Checkable;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\Tasks\CreateCcdaFromAthenaApi;

/**
 * This class encapsulates the logic for creating an Checkable for AthenaApiImplementation patients.
 *
 * @see \CircleLinkHealth\Eligibility\Contracts\Checkable
 *
 * Class AthenaEligibilityCheckableFactory
 */
class AthenaEligibilityCheckableFactory
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApi;
    
    public function __construct(AthenaApiImplementation $athenaApi)
    {
        $this->athenaApi = $athenaApi;
    }
    
    /**
     * @param TargetPatient $targetPatient
     *
     * @return MedicalRecord|null
     * @throws \Exception
     */
    public static function getCCDFromAthenaApi(TargetPatient $targetPatient): ?MedicalRecord
    {
        try {
            return app(CreateCcdaFromAthenaApi::class)->handle($targetPatient);
        } catch (CcdaWasNotFetchedFromAthenaApi $e) {
            $targetPatient->setStatusFromException($e);
            $targetPatient->save();
        }
        
        return null;
    }
    
    /**
     * @param TargetPatient $targetPatient
     *
     * @return AthenaCheckable
     * @throws Exception
     *
     */
    public function makeAthenaEligibilityCheckable(TargetPatient $targetPatient): AthenaCheckable
    {
        $ccda = $targetPatient->ccda;
        
        if ( ! $ccda) {
            self::getCCDFromAthenaApi($targetPatient);
        }
        
        return new AthenaCheckable($ccda, $targetPatient->practice, $targetPatient->batch, $targetPatient);
    }
}
