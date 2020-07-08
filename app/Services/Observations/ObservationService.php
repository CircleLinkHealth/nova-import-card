<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Observations;

class ObservationService
{
    public static function getObsKey(?string $key)
    {
        if ( ! $key) {
            return null;
        }
        if (array_key_exists($key, ObservationConstants::MEDICATIONS)) {
            return ObservationConstants::ADHERENCE;
        }
        if (array_key_exists($key, ObservationConstants::LIFESTYLE)) {
            return ObservationConstants::LIFESTYLE_OBSERVATION_TYPE;
        }
        if (array_key_exists($key, ObservationConstants::BIOMETRICS)) {
            return ObservationConstants::BIOMETRICS[$key];
        }
        if (array_key_exists($key, ObservationConstants::SYMPTOMS)) {
            return ObservationConstants::SYMPTOMS_OBSERVATION_TYPE;
        }

        return null;
    }
}
