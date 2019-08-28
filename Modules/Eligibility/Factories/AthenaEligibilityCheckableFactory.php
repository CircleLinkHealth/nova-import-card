<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Models\MedicalRecords\Ccda;
use App\TargetPatient;
use CircleLinkHealth\Eligibility\Checkables\AthenaPatient;

/**
 * This class encapsulates the logic for creating EligibilityCheckables for AthenaApi patients.
 *
 * @see App\Contracts\EligibilityCheckable
 *
 * Class AthenaEligibilityCheckableFactory
 */
class AthenaEligibilityCheckableFactory
{
    /**
     * @param TargetPatient $targetPatient
     *
     * @return AthenaPatient
     */
    public function makeAthenaPatientFromApi(TargetPatient $targetPatient): AthenaPatient
    {
        $ccda = $targetPatient->ccda;

        if ( ! $ccda) {
            $ccda = $this->createCcdaFromAthenaApi($targetPatient);
        }

        return new AthenaPatient($ccda, $targetPatient->practice, $targetPatient->batch, $targetPatient);
    }

    /**
     * @param TargetPatient $targetPatient
     *
     * @return \App\Importer\MedicalRecordEloquent|bool|Ccda|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private function createCcdaFromAthenaApi(TargetPatient $targetPatient)
    {
        $ccdaExternal = app('athena.api')->getCcd(
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
