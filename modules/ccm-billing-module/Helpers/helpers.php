<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Core\Entities\AppConfig;

if ( ! function_exists('complexAttestationRequirementsEnabledForPractice')) {
    /**
     * @param mixed $practiceId
     */
    function complexAttestationRequirementsEnabledForPractice($practiceId): bool
    {
        $key = 'complex_attestation_requirements_for_practice';

        $val = AppConfig::pull($key, null);
        if (null === $val) {
            AppConfig::set($key, '');

            $practiceIds = [];
        } else {
            $practiceIds = explode(',', $val);
        }

        return in_array($practiceId, $practiceIds) || in_array('all', $practiceIds);
    }
}
