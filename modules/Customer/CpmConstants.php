<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer;

use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem;

class CpmConstants
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
        CpmConstants::ICD9_NAME   => 1,
        CpmConstants::ICD10_NAME  => 2,
        CpmConstants::SNOMED_NAME => 3,
    ];
    const CPM_PATIENTS_AND_SURVEY_ONLY_PATIENTS = ['participant', 'survey-only'];
    const FIFO_QUEUE                            = 'fifo';
    const FIVE_MINUTES_IN_SECONDS               = 300;
    const FORTY_MINUTES_IN_SECONDS              = 2400;
    public const FROM_CALLBACK_EMAIL_DOMAIN     = 'callcenterusa.net';
    public const FROM_CALLBACK_MAIL             = 'message.dispatch@callcenterusa.net';
    public const FROM_ETHAN_MAIL                = 'ethan@circlelinkhealth.com';
    /**
     * See "CPM Queues" in config/queue.php
     * Jobs can live in a module, and therefore dispatched by different apps.
     * Consider StoreTimeTracking. It may be dispatched from admin or provider app
     * to queue "high". For this reason we need a unique name for "high" and "low"
     * queues in each app.
     */
    const HIGH_QUEUE = 'high';
    const ICD10      = ProblemCodeSystem::ICD10;
    const ICD10_NAME = ProblemCodeSystem::ICD10_NAME;

    /**
     * Problem Codes.
     */
    const ICD9 = ProblemCodeSystem::ICD9;

    const ICD9_NAME                                      = ProblemCodeSystem::ICD9_NAME;
    const LOW_QUEUE                                      = 'low';
    const MONTHLY_BILLABLE_CCM_40_TIME_TARGET_IN_SECONDS = 2400;
    const MONTHLY_BILLABLE_CCM_60_TIME_TARGET_IN_SECONDS = 3600;
    const MONTHLY_BILLABLE_PCM_TIME_TARGET_IN_SECONDS    = 1800;

    const MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS = 1200;

    //Groups for Nova Resources
    const NOVA_GROUP_ADMIN               = 'Admin';
    const NOVA_GROUP_CARE_COACHES        = 'Care Coaches';
    const NOVA_GROUP_ENROLLMENT          = 'Enrollment';
    const NOVA_GROUP_NBI                 = 'NBI';
    const NOVA_GROUP_PRACTICE_DATA_PULLS = 'Practice Data Pulls';
    const NOVA_GROUP_PRACTICES           = 'Practices';
    const NOVA_GROUP_SELF_ENROLLMENT     = 'Self Enrollment';
    const NOVA_GROUP_SETTINGS            = 'Settings';

    const PATIENT_PHI_RELATIONSHIPS = ['patientInfo'];

    const PRACTICE_STAFF_ROLE_NAMES   = ['provider', 'office_admin', 'med_assistant', 'registered-nurse', 'specialist'];
    public const PRACTICE_STAFF_ROLES = [
        'practice-lead',
        'med_assistant',
        'office_admin',
        'provider',
        'registered-nurse',
        'specialist',
        'software-only',
        'care-center-external',
    ];
    const SAAS_INTERNAL_USER_ROLE_NAMES          = ['saas-admin', 'care-center'];
    public const SCHEDULER_POSTMARK_INBOUND_MAIL = 'postmark_inbound_mail';
    const SIXTY_MINUTES_IN_SECONDS               = 3600;

    const SNOMED                 = ProblemCodeSystem::SNOMED;
    const SNOMED_NAME            = ProblemCodeSystem::SNOMED_NAME;
    const TEN_MINUTES_IN_SECONDS = 600;

    const THIRTY_DAYS_IN_MINUTES     = 43200;
    const THIRTY_MINUTES_IN_SECONDS  = 1800;
    const TRIX_ALLOWABLE_TAGS_STRING = '<div><strong><h1><em><del><blockquote><pre><br><ul><ol><li><span><a>';
    const TRIX_FIELDS                = ['patient-email-body'];
    const TWENTY_MINUTES_IN_SECONDS  = 1200;
    const VIEWING_PATIENT            = 'viewing-patient';

    public static function athenaEhrId()
    {
        return \Cache::remember('athena_ehr_id_in_cpm', 2, function () {
            return optional(Ehr::whereName('Athena')->firstOrFail())->id;
        });
    }
}
