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

    /**
     * To create more patients, simply add to the array below, following the existing array structure.
     */
    const CBT_TEST_PATIENTS = [
        'patient_1' => [
            //user
            'first_name' => 'CBT',
            'last_name'  => 'Automation 1',
            'email'      => 'cbtAutomation1@test.com',
            //use name to get practice id for 'program_id'
            'practice_name'       => 'demo',
            'billing_provider_id' => 13242,

            //patient_info
            'gender'                     => 'M',
            'preferred_contact_language' => 'EN',
            'ccm_status'                 => 'enrolled',
            'birth_date'                 => '1945-11-27',
            'consent_date'               => '2019-03-13',
            'preferred_contact_timezone' => 'America/New_York',
            'mrn_number'                 => 236025386923,

            'conditions' => [
                'all',
            ],
            //number of dummy medications
            'medications' => 25,
        ],
        'patient_2' => [
            //user
            'first_name' => 'CBT',
            'last_name'  => 'Automation 2',
            'email'      => 'cbtAutomation2@test.com',
            //use name to get practice id for 'program_id'
            'practice_name'       => 'demo',
            'billing_provider_id' => 13242,

            //patient_info
            'gender'                     => 'F',
            'preferred_contact_language' => 'EN',
            'ccm_status'                 => 'enrolled',
            'birth_date'                 => '1927-12-07',
            'consent_date'               => '2018-05-27',
            'preferred_contact_timezone' => 'America/New_York',
            'mrn_number'                 => 186027387923,

            'conditions' => [
                'Hypertension',
                'Dementia',
                'Diabetes Type 2',
            ],
            //number of dummy medications
            'medications' => 0,
        ],
    ];

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

    //Groups for Nova Resources
    const NOVA_GROUP_CARE_COACHES = 'Care Coaches';
    const NOVA_GROUP_ENROLLMENT   = 'Enrollment';
    const NOVA_GROUP_NBI          = 'NBI';
    const NOVA_GROUP_PRACTICES    = 'Practices';

    const PATIENT_PHI_RELATIONSHIPS = ['patientInfo'];

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
    const SNOMED                     = 'snomed_code';
    const SNOMED_NAME                = 'SNOMED CT';
    const TRIX_ALLOWABLE_TAGS_STRING = '<div><strong><h1><em><del><blockquote><pre><br><ul><ol><li><span>';
    const TRIX_FIELDS                = ['patient-email-body'];
    const VIEWING_PATIENT            = 'viewing-patient';

    /**
     * @param int $i
     *
     * Generate dummy medication names for users
     *
     * @return array
     */
    public static function testMedications($i = 25)
    {
        $medications = [];
        while ($i > 0) {
            $medications[] = ['name' => 'med'.' '.$i];
            --$i;
        }

        return $medications;
    }
}
