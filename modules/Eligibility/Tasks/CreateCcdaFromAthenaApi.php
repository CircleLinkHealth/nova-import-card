<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tasks;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Exceptions\CcdaWasNotFetchedFromAthenaApi;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Facades\DB;

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
     * @throws CcdaWasNotFetchedFromAthenaApi
     */
    public function handle(TargetPatient &$targetPatient): Ccda
    {
        if ($targetPatient->ccda && ! empty($targetPatient->ccda->json)) {
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

        $ccda = Ccda::create(
            [
                'practice_id' => $targetPatient->practice_id,
                'xml'         => $ccdaExternal[0]['ccda'],
                'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                'source'      => Ccda::ATHENA_API,
                'imported'    => false,
                'batch_id'    => $targetPatient->batch_id,
            ]
        );

        $targetPatient->ccda_id = $ccda->id;
        $targetPatient->save();

        DB::commit();

        return $ccda;
    }
}
