<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

class Constants
{
    const CACHE_USER_HAS_CCDA = 'user:{$userId}:has_ccda';
    /**
     * Redis Cache Keys.
     */
    const CACHED_USER_NOTIFICATIONS = 'user:{$userId}:notifications';

    const CLH_INTERNAL_USER_ROLE_NAMES         = ['saas-admin', 'care-center', 'administrator'];
    const CLOUD_CCDAS_PROCESS_ELIGIBILITY_PATH = '/eligibility/ccdas';

    /**
     * S3 `cloud` disk storage.
     */
    const CLOUD_LISTS_PROCESS_ELIGIBILITY_PATH = '/eligibility/lists';

    const CODE_SYSTEM_NAME_ID_MAP = [
        Constants::ICD9_NAME   => 1,
        Constants::ICD10_NAME  => 2,
        Constants::SNOMED_NAME => 3,
    ];
    const ICD10      = 'icd_10_code';
    const ICD10_NAME = 'ICD-10';

    /**
     * Problem Codes.
     */
    const ICD9 = 'icd_9_code';

    const ICD9_NAME = 'ICD-9';

    const MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS = 1200;

    const NOVA_GROUP_CARE_COACHES       = 'Care Coaches';
    const PRACTICE_STAFF_ROLE_NAMES     = ['provider', 'office_admin', 'med_assistant', 'registered-nurse', 'specialist'];
    const SAAS_INTERNAL_USER_ROLE_NAMES = ['saas-admin', 'care-center'];

    /**
     * These settings match CLH mail vendor's envelopes.
     */
    const SNAPPY_CLH_MAIL_VENDOR_SETTINGS = [
        'disable-javascript' => true,
        'margin-top'         => '12',
        'margin-left'        => '25',
        'margin-bottom'      => '15',
        'margin-right'       => '0.75',
    ];
    const SNOMED      = 'snomed_code';
    const SNOMED_NAME = 'SNOMED CT';
}
