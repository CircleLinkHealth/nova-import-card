<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Disabling cache
    |--------------------------------------------------------------------------
    |
    | By setting this value to false, the cache will be disabled completely.
    | This may be useful for debugging purposes.
    |
    */
    'active' => env('LADA_CACHE_ACTIVE', true),

    /*
    |--------------------------------------------------------------------------
    | Redis prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be prepended to all items in Redis store.
    | Do not change this value in production, it will cause unexpected behavior.
    |
    */
    'prefix' => 'lada:',

    /*
    |--------------------------------------------------------------------------
    | Expiration time
    |--------------------------------------------------------------------------
    |
    | By default, if this value is set to null, cached items will never expire.
    | If you are afraid of dead data or if you care about disk space, it may
    | be a good idea to set this value to something like 604800 (7 days).
    |
    */
    'expiration-time' => 86400,

    /*
    |--------------------------------------------------------------------------
    | Cache granularity
    |--------------------------------------------------------------------------
    |
    | If you experience any issues while using the cache, try to set this value
    | to false. This will tell the cache to use a lower granularity and not
    | consider the row primary keys when creating the tags for a database query.
    | Since this will dramatically reduce the efficiency of the cache, it is
    | not recommended to do so in production environment.
    |
    */
    'consider-rows' => true,

    /*
    |--------------------------------------------------------------------------
    | Include tables
    |--------------------------------------------------------------------------
    |
    | If you want to cache only specific tables, put the table names into this
    | array. Then as soon as a query contains a table which is not specified in
    | here, it will not be cached. If you have this feature enabled, the value
    | of "exclude-tables" will be ignored and has no effect.
    |
    | Instead of hard coding table names in the configuration, it is a good
    | practice to initialize a new model instance and get the table name from
    | there like in the following example:
    |
    | 'include-tables' => [
    |     (new \App\Models\User())->getTable(),
    |     (new \App\Models\Post())->getTable(),
    | ],
    |
    */
    'include-tables' => [
        'addendums',
        'app_config',
        'appointments',
        'authy_users',
        'browsers',
        'calls',
        'calls_view',
        'care_plan_templates',
        'care_plan_templates_cpm_biometrics',
        'care_plan_templates_cpm_lifestyles',
        'care_plan_templates_cpm_medication_groups',
        'care_plan_templates_cpm_problems',
        'care_plan_templates_cpm_symptoms',
        'care_plans',
        'careplan_print_list_view',
        'ccd_allergies',
        'ccd_insurance_policies',
        'ccd_medications',
        'ccd_problems',
        'ccdas',
        'chargeable_services',
        'chargeables',
        'company_holidays',
        'contacts',
        'cpm_biometrics',
        'cpm_biometrics_users',
        'cpm_blood_pressures',
        'cpm_blood_sugars',
        'cpm_instructions',
        'cpm_lifestyles',
        'cpm_lifestyles_users',
        'cpm_medication_groups',
        'cpm_medication_groups_users',
        'cpm_problems',
        'cpm_problems_activate_cpm_biometrics',
        'cpm_problems_activate_cpm_lifestyles',
        'cpm_problems_activate_cpm_medication_groups',
        'cpm_problems_activate_cpm_symptoms',
        'cpm_problems_users',
        'cpm_settings',
        'cpm_smokings',
        'cpm_symptoms',
        'cpm_symptoms_users',
        'cpm_weights',
        'ehrs',
        'email_settings',
        'emr_direct_addresses',
        'families',
        'holidays',
        'instructables',
        'location_user',
        'locations',
        'lv_activities',
        'lv_observationmeta',
        'lv_observations',
        'lv_permissions',
        'lv_roles',
        'media',
        'medication_groups_maps',
        'notes',
        'notifications',
        'nurse_contact_window',
        'nurse_info',
        'nurse_info_state',
        'nurse_monthly_summaries',
        'offline_activity_time_requests',
        'patient_care_team_members',
        'patient_contact_window',
        'patient_info',
        'patient_monthly_summaries',
        'patient_summary_problems',
        'permissibles',
        'phone_numbers',
        'practice_role_user',
        'practices',
        'problem_code_systems',
        'problem_codes',
        'provider_info',
        'saas_accounts',
        'snomed_to_cpm_icd_maps',
        'states',
        'notes',
        'users',
        'users_password_history',
        'work_hours',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude tables
    |--------------------------------------------------------------------------
    |
    | If you want to cache all tables but some specific ones, put them into this
    | array. As soon as a query contains at least one table specified in here, it
    | will not be cached.
    |
    */
    'exclude-tables' => [
        //        (new PageTimer())->getTable(),
        //        (new Activity())->getTable(),
    ],
];
