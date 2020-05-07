<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Contracts\EligibilityCheckAdapter;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class CcdaToEligibilityJobAdapter implements EligibilityCheckAdapter
{
    use CreatesEligibilityJobFromObject;
    /**
     * @var EligibilityBatch
     */
    protected $batch;
    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var \CircleLinkHealth\SharedModels\Entities\Ccda
     */
    private $ccda;

    public function __construct(Ccda $ccda, Practice $practice, EligibilityBatch $batch)
    {
        $this->ccda     = $ccda;
        $this->practice = $practice;
        $this->batch    = $batch;
    }

    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        $eJ = $this->createFromBlueButtonObject($this->ccda->bluebuttonJson(), $this->batch, $this->practice);

        $this->ccda->practice_id = $this->practice->id;

        if ($this->ccda->isDirty()) {
            $this->ccda->save();
        }

        $data                        = $eJ->data;
        $data['medical_record_type'] = Ccda::class;
        $data['medical_record_id']   = $this->ccda->id;
        $eJ->data                    = $data;
        $eJ->save();

        return $eJ;
    }

    public function getMedicalRecord(): MedicalRecord
    {
        return $this->ccda;
    }
}
