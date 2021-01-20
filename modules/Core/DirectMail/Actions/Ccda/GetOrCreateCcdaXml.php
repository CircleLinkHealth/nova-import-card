<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\DirectMail\Actions\Ccda;

use CircleLinkHealth\Customer\Entities\User;

class GetOrCreateCcdaXml
{
    public static function forPatient(User $patient): ?string
    {
        $q = $patient->ccdas()->orderByDesc('id')->with('media');

        if ($ccda = $q->has('media')->first()) {
            return optional($ccda->getMedia('ccd')->first())->getFile();
        }

        if ($ccda = $q->first()) {
            return (new \CircleLinkHealth\Core\DirectMail\Adapters\Ccda\PracticePullMedicalRecordToXmlAdapter($ccda))
                ->createAndStoreXml();
        }

        return null;
    }
}
