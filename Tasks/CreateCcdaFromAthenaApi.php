<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tasks;



use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Exceptions\CcdaWasNotFetchedFromAthenaApi;
use CircleLinkHealth\SharedModels\Entities\Ccda;

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
     * @return \App\Importer\MedicalRecordEloquent|bool|Ccda|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function handle(TargetPatient &$targetPatient)
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
            throw new CcdaWasNotFetchedFromAthenaApi($targetPatient);
        }

        return tap(
            Ccda::create(
                [
                    'practice_id' => $targetPatient->practice_id,
                    'xml'         => $ccdaExternal[0]['ccda'],
                    'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                    'source'      => Ccda::ATHENA_API,
                    'imported'    => false,
                    'batch_id'    => $targetPatient->batch_id,
                ]
            ),
            function (Ccda $ccda) use (&$targetPatient) {
                $targetPatient->ccda_id = $ccda->id;
                $targetPatient->save();
            }
        );
    }
}
