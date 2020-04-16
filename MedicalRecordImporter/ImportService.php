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
    public static function replaceCpmValues(Ccda $ccda, ?Enrollee $enrollee)
    {
        if ( ! $ccda->practice_id) {
            $ccda->practice_id = $enrollee->practice_id;
        }
        if ( ! $ccda->location_id) {
            $ccda->location_id = $enrollee->location_id;
        }
        if ( ! $ccda->billing_provider_id) {
            $ccda->billing_provider_id = $enrollee->billing_provider_user_id;
        }
        if ($ccda->isDirty()) {
            $ccda->save();
        }
        
        return $ccda;
    }
    
    /**
     * Import a Patient whose CCDA we have already.
     *
     * @param $ccdaId
     *
     * @param Enrollee|null $enrollee
     *
     * @return User|null
     */
    public function importExistingCcda($ccdaId, Enrollee $enrollee = null): ?User
    {
        /** @var Ccda $ccda */
        $ccda = Ccda::withTrashed()
                    ->with(['patient.patientInfo', 'media'])
                    ->find($ccdaId);
        
        if ( ! $ccda) {
            return null;
        }
        
        $ccda = self::replaceCpmValues($ccda, $enrollee);
        
        if ($ccda->imported) {
            if ($ccda->patient) {
                return $ccda->patient;
            }
        }
        
        if ($ccda->patientMrn() && $ccda->practice_id) {
            $exists = User::whereHas(
                'patientInfo',
                function ($q) use ($ccda) {
                    $q->where('mrn_number', $ccda->patientMrn());
                }
            )->whereProgramId($ccda->practice_id)
                          ->first();
            
            if ($exists) {
                return $exists;
            }
        }
        
        $ccda = $ccda->import($enrollee);
        
        $ccda->status   = Ccda::QA;
        $ccda->imported = true;
        $ccda->save();
        
        return $ccda->patient;
    }
    
    public function isCcda($medicalRecordType)
    {
        return stripcslashes($medicalRecordType) == stripcslashes(Ccda::class);
    }
}
