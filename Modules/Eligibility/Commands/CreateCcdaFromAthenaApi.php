<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Commands;

use App\Models\MedicalRecords\Ccda;
use App\TargetPatient;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;

class CreateCcdaFromAthenaApi
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApiImplementation;

    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        $this->athenaApiImplementation = $athenaApiImplementation;
    }

    /**
     * @param TargetPatient $targetPatient
     *
     * @return \App\Importer\MedicalRecordEloquent|bool|Ccda|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function handle(TargetPatient $targetPatient)
    {
        if ($targetPatient->ccda) {
            return $targetPatient->ccda;
        }

        $ccdaExternal = $this->athenaApiImplementation->getCcd(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );

        if ( ! isset($ccdaExternal[0])) {
            \Log::error('Could not retrieve CCD from Athena for '.TargetPatient::class.':'.$targetPatient->id);

            return false;
        }

        return Ccda::create(
            [
                'practice_id' => $targetPatient->practice_id,
                'vendor_id'   => 1,
                'xml'         => $ccdaExternal[0]['ccda'],
                'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                'source'      => Ccda::ATHENA_API,
                'imported'    => false,
                'batch_id'    => $targetPatient->batch_id,
            ]
        );
    }
}
