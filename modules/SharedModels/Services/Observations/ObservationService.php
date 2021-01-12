<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Observations;

class ObservationService
{
    public static function getObsKey(?string $key)
    {
        if (array_key_exists($key, ObservationConstants::ACCEPTED_OBSERVATION_TYPES)) {
            $obsType = ObservationConstants::ACCEPTED_OBSERVATION_TYPES[$key];
            if (ObservationConstants::BIOMETRICS_ADHERENCE_OBSERVATION_TYPE == $obsType['category_name']) {
                return $obsType['name'];
            }

            return $obsType['category_name'];
        }

        return null;
    }
}
