<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Actions\Ccda;

use App\Adapters\Ccda\PracticePullMedicalRecordToXmlAdapter;
use CircleLinkHealth\Customer\Entities\User;

class GetOrCreateCcdaXml
{
    public static function forPatient(User $patient)
    {
        $q = $patient->ccdas()->orderByDesc('id')->with('media');

        if ($ccda = $q->has('media')->first()) {
            return optional($ccda->getMedia('ccd')->first())->getFile();
        }

        if ($ccda = $q->first()) {
            return (new PracticePullMedicalRecordToXmlAdapter($ccda))
                ->createAndStoreXml();
        }

        return null;
    }
}
