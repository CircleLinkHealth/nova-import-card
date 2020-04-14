<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class ImportService
{
    /**
     * Import a Patient whose CCDA we have already.
     *
     * @param $ccdaId
     *
     * @param Enrollee|null $enrollee
     *
     * @return \stdClass
     */
    public function importExistingCcda($ccdaId, Enrollee $enrollee = null)
    {
        $response = new \stdClass();

        $ccda = Ccda::withTrashed()
            ->with(['patient.patientInfo', 'media'])
            ->find($ccdaId);

        if ( ! $ccda) {
            $response->success = false;
            $response->message = "We could not locate CCDA with id ${ccdaId}";
            $response->imr     = null;

            return $response;
        }

        if ($ccda->imported) {
            if ($ccda->patient) {
                $response->success = false;
                $response->message = "CCDA with id ${ccdaId} has already been imported.";
                $response->imr     = null;

                return $response;
            }
        }

        if ($ccda->mrn && $ccda->practice_id) {
            $exists = User::whereHas(
                'patientInfo',
                function ($q) use ($ccda) {
                    $q->where('mrn_number', $ccda->mrn);
                }
            )->whereProgramId($ccda->practice_id)
                ->exists();

            if ($exists) {
                $response->success = false;
                $response->message = "CCDA with id ${ccdaId} has already been imported.";
                $response->imr     = null;

                return $response;
            }
        }

        $imr = $ccda->import($enrollee);

        $ccda->status   = Ccda::QA;
        $ccda->imported = true;
        $ccda->save();

        $response->success = true;
        $response->message = 'CCDA successfully imported.';
        $response->imr     = $imr;

        return $response;
    }

    public function isCcda($medicalRecordType)
    {
        return stripcslashes($medicalRecordType) == stripcslashes(Ccda::class);
    }
}
