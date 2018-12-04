<?php

use Illuminate\Database\Seeder;

class MigrationsTableSeeder extends Seeder
{

    /**
     * Migrations Data showing state of CPM-WEB DB as at 2018/05/07
     *
     * should be executed on any previously running platform where this GIT branch is to be used
     *
     * @return void
     */
    public function run()
    {
        \DB::table('migrations')->delete();

        \DB::table('migrations')->insert([
            
        0 => [
            'id' => 1,
            'migration' => '2016_06_01_000001_create_oauth_auth_codes_table',
            'batch' => 1
        ],
    

        1 => [
            'id' => 2,
            'migration' => '2016_06_01_000002_create_oauth_access_tokens_table',
            'batch' => 1
        ],
    

        2 => [
            'id' => 3,
            'migration' => '2016_06_01_000003_create_oauth_refresh_tokens_table',
            'batch' => 1
        ],
    

        3 => [
            'id' => 4,
            'migration' => '2016_06_01_000004_create_oauth_clients_table',
            'batch' => 1
        ],
    

        4 => [
            'id' => 5,
            'migration' => '2016_06_01_000005_create_oauth_personal_access_clients_table',
            'batch' => 1
        ],
    

        5 => [
            'id' => 6,
            'migration' => '2018_05_07_062243_create_addendums_table',
            'batch' => 1
        ],
    

        6 => [
            'id' => 7,
            'migration' => '2018_05_07_062243_create_allergy_imports_table',
            'batch' => 1
        ],
    

        7 => [
            'id' => 8,
            'migration' => '2018_05_07_062243_create_app_config_table',
            'batch' => 1
        ],
    

        8 => [
            'id' => 9,
            'migration' => '2018_05_07_062243_create_appointments_table',
            'batch' => 1
        ],
    

        9 => [
            'id' => 10,
            'migration' => '2018_05_07_062243_create_calls_table',
            'batch' => 1
        ],
    

        10 => [
            'id' => 11,
            'migration' => '2018_05_07_062243_create_care_ambassador_logs_table',
            'batch' => 1
        ],
    

        11 => [
            'id' => 12,
            'migration' => '2018_05_07_062243_create_care_ambassadors_table',
            'batch' => 1
        ],
    

        12 => [
            'id' => 13,
            'migration' => '2018_05_07_062243_create_care_items_table',
            'batch' => 1
        ],
    

        13 => [
            'id' => 14,
            'migration' => '2018_05_07_062243_create_care_plan_templates_cpm_biometrics_table',
            'batch' => 1
        ],
    

        14 => [
            'id' => 15,
            'migration' => '2018_05_07_062243_create_care_plan_templates_cpm_lifestyles_table',
            'batch' => 1
        ],
    

        15 => [
            'id' => 16,
            'migration' => '2018_05_07_062243_create_care_plan_templates_cpm_medication_groups_table',
            'batch' => 1
        ],
    

        16 => [
            'id' => 17,
            'migration' => '2018_05_07_062243_create_care_plan_templates_cpm_miscs_table',
            'batch' => 1
        ],
    

        17 => [
            'id' => 18,
            'migration' => '2018_05_07_062243_create_care_plan_templates_cpm_problems_table',
            'batch' => 1
        ],
    

        18 => [
            'id' => 19,
            'migration' => '2018_05_07_062243_create_care_plan_templates_cpm_symptoms_table',
            'batch' => 1
        ],
    

        19 => [
            'id' => 20,
            'migration' => '2018_05_07_062243_create_care_plan_templates_table',
            'batch' => 1
        ],
    

        20 => [
            'id' => 21,
            'migration' => '2018_05_07_062243_create_care_plans_table',
            'batch' => 1
        ],
    

        21 => [
            'id' => 22,
            'migration' => '2018_05_07_062243_create_care_sections_table',
            'batch' => 1
        ],
    

        22 => [
            'id' => 23,
            'migration' => '2018_05_07_062243_create_careplan_assessments_table',
            'batch' => 1
        ],
    

        23 => [
            'id' => 24,
            'migration' => '2018_05_07_062243_create_ccd_allergies_table',
            'batch' => 1
        ],
    

        24 => [
            'id' => 25,
            'migration' => '2018_05_07_062243_create_ccd_allergy_logs_table',
            'batch' => 1
        ],
    

        25 => [
            'id' => 26,
            'migration' => '2018_05_07_062243_create_ccd_demographics_logs_table',
            'batch' => 1
        ],
    

        26 => [
            'id' => 27,
            'migration' => '2018_05_07_062243_create_ccd_document_logs_table',
            'batch' => 1
        ],
    

        27 => [
            'id' => 28,
            'migration' => '2018_05_07_062243_create_ccd_import_routines_strategies_table',
            'batch' => 1
        ],
    

        28 => [
            'id' => 29,
            'migration' => '2018_05_07_062243_create_ccd_import_routines_table',
            'batch' => 1
        ],
    

        29 => [
            'id' => 30,
            'migration' => '2018_05_07_062243_create_ccd_insurance_policies_table',
            'batch' => 1
        ],
    

        30 => [
            'id' => 31,
            'migration' => '2018_05_07_062243_create_ccd_medication_logs_table',
            'batch' => 1
        ],
    

        31 => [
            'id' => 32,
            'migration' => '2018_05_07_062243_create_ccd_medications_table',
            'batch' => 1
        ],
    

        32 => [
            'id' => 33,
            'migration' => '2018_05_07_062243_create_ccd_problem_code_logs_table',
            'batch' => 1
        ],
    

        33 => [
            'id' => 34,
            'migration' => '2018_05_07_062243_create_ccd_problem_logs_table',
            'batch' => 1
        ],
    

        34 => [
            'id' => 35,
            'migration' => '2018_05_07_062243_create_ccd_problems_table',
            'batch' => 1
        ],
    

        35 => [
            'id' => 36,
            'migration' => '2018_05_07_062243_create_ccd_provider_logs_table',
            'batch' => 1
        ],
    

        36 => [
            'id' => 37,
            'migration' => '2018_05_07_062243_create_ccd_vendors_table',
            'batch' => 1
        ],
    

        37 => [
            'id' => 38,
            'migration' => '2018_05_07_062243_create_ccda_requests_table',
            'batch' => 1
        ],
    

        38 => [
            'id' => 39,
            'migration' => '2018_05_07_062243_create_ccdas_table',
            'batch' => 1
        ],
    

        39 => [
            'id' => 40,
            'migration' => '2018_05_07_062243_create_ccm_time_api_logs_table',
            'batch' => 1
        ],
    

        40 => [
            'id' => 41,
            'migration' => '2018_05_07_062243_create_chargeable_services_table',
            'batch' => 1
        ],
    

        41 => [
            'id' => 42,
            'migration' => '2018_05_07_062243_create_chargeables_table',
            'batch' => 1
        ],
    

        42 => [
            'id' => 43,
            'migration' => '2018_05_07_062243_create_contacts_table',
            'batch' => 1
        ],
    

        43 => [
            'id' => 44,
            'migration' => '2018_05_07_062243_create_cpm_biometrics_table',
            'batch' => 1
        ],
    

        44 => [
            'id' => 45,
            'migration' => '2018_05_07_062243_create_cpm_biometrics_users_table',
            'batch' => 1
        ],
    

        45 => [
            'id' => 46,
            'migration' => '2018_05_07_062243_create_cpm_blood_pressures_table',
            'batch' => 1
        ],
    

        46 => [
            'id' => 47,
            'migration' => '2018_05_07_062243_create_cpm_blood_sugars_table',
            'batch' => 1
        ],
    

        47 => [
            'id' => 48,
            'migration' => '2018_05_07_062243_create_cpm_instructions_table',
            'batch' => 1
        ],
    

        48 => [
            'id' => 49,
            'migration' => '2018_05_07_062243_create_cpm_lifestyles_table',
            'batch' => 1
        ],
    

        49 => [
            'id' => 50,
            'migration' => '2018_05_07_062243_create_cpm_lifestyles_users_table',
            'batch' => 1
        ],
    

        50 => [
            'id' => 51,
            'migration' => '2018_05_07_062243_create_cpm_medication_groups_table',
            'batch' => 1
        ],
    

        51 => [
            'id' => 52,
            'migration' => '2018_05_07_062243_create_cpm_medication_groups_users_table',
            'batch' => 1
        ],
    

        52 => [
            'id' => 53,
            'migration' => '2018_05_07_062243_create_cpm_miscs_table',
            'batch' => 1
        ],
    

        53 => [
            'id' => 54,
            'migration' => '2018_05_07_062243_create_cpm_miscs_users_table',
            'batch' => 1
        ],
    

        54 => [
            'id' => 55,
            'migration' => '2018_05_07_062243_create_cpm_problems_activate_cpm_biometrics_table',
            'batch' => 1
        ],
    

        55 => [
            'id' => 56,
            'migration' => '2018_05_07_062243_create_cpm_problems_activate_cpm_lifestyles_table',
            'batch' => 1
        ],
    

        56 => [
            'id' => 57,
            'migration' => '2018_05_07_062243_create_cpm_problems_activate_cpm_medication_groups_table',
            'batch' => 1
        ],
    

        57 => [
            'id' => 58,
            'migration' => '2018_05_07_062243_create_cpm_problems_activate_cpm_symptoms_table',
            'batch' => 1
        ],
    

        58 => [
            'id' => 59,
            'migration' => '2018_05_07_062243_create_cpm_problems_table',
            'batch' => 1
        ],
    

        59 => [
            'id' => 60,
            'migration' => '2018_05_07_062243_create_cpm_problems_users_table',
            'batch' => 1
        ],
    

        60 => [
            'id' => 61,
            'migration' => '2018_05_07_062243_create_cpm_settings_table',
            'batch' => 1
        ],
    

        61 => [
            'id' => 62,
            'migration' => '2018_05_07_062243_create_cpm_smokings_table',
            'batch' => 1
        ],
    

        62 => [
            'id' => 63,
            'migration' => '2018_05_07_062243_create_cpm_symptoms_table',
            'batch' => 1
        ],
    

        63 => [
            'id' => 64,
            'migration' => '2018_05_07_062243_create_cpm_symptoms_users_table',
            'batch' => 1
        ],
    

        64 => [
            'id' => 65,
            'migration' => '2018_05_07_062243_create_cpm_weights_table',
            'batch' => 1
        ],
    

        65 => [
            'id' => 66,
            'migration' => '2018_05_07_062243_create_days_of_week_table',
            'batch' => 1
        ],
    

        66 => [
            'id' => 67,
            'migration' => '2018_05_07_062243_create_demographics_imports_table',
            'batch' => 1
        ],
    

        67 => [
            'id' => 68,
            'migration' => '2018_05_07_062243_create_ehrs_table',
            'batch' => 1
        ],
    

        68 => [
            'id' => 69,
            'migration' => '2018_05_07_062243_create_eligibility_batches_table',
            'batch' => 1
        ],
    

        69 => [
            'id' => 70,
            'migration' => '2018_05_07_062243_create_eligibility_jobs_table',
            'batch' => 1
        ],
    

        70 => [
            'id' => 71,
            'migration' => '2018_05_07_062243_create_email_settings_table',
            'batch' => 1
        ],
    

        71 => [
            'id' => 72,
            'migration' => '2018_05_07_062243_create_emr_direct_addresses_table',
            'batch' => 1
        ],
    

        72 => [
            'id' => 73,
            'migration' => '2018_05_07_062243_create_enrollees_table',
            'batch' => 1
        ],
    

        73 => [
            'id' => 74,
            'migration' => '2018_05_07_062243_create_exceptions_table',
            'batch' => 1
        ],
    

        74 => [
            'id' => 75,
            'migration' => '2018_05_07_062243_create_failed_jobs_table',
            'batch' => 1
        ],
    

        75 => [
            'id' => 76,
            'migration' => '2018_05_07_062243_create_families_table',
            'batch' => 1
        ],
    

        76 => [
            'id' => 77,
            'migration' => '2018_05_07_062243_create_fax_logs_table',
            'batch' => 1
        ],
    

        77 => [
            'id' => 78,
            'migration' => '2018_05_07_062243_create_foreign_ids_table',
            'batch' => 1
        ],
    

        78 => [
            'id' => 79,
            'migration' => '2018_05_07_062243_create_holidays_table',
            'batch' => 1
        ],
    

        79 => [
            'id' => 80,
            'migration' => '2018_05_07_062243_create_imported_medical_records_table',
            'batch' => 1
        ],
    

        80 => [
            'id' => 81,
            'migration' => '2018_05_07_062243_create_instructables_table',
            'batch' => 1
        ],
    

        81 => [
            'id' => 82,
            'migration' => '2018_05_07_062243_create_insurance_logs_table',
            'batch' => 1
        ],
    

        82 => [
            'id' => 83,
            'migration' => '2018_05_07_062243_create_invites_table',
            'batch' => 1
        ],
    

        83 => [
            'id' => 84,
            'migration' => '2018_05_07_062243_create_jobs_table',
            'batch' => 1
        ],
    

        84 => [
            'id' => 85,
            'migration' => '2018_05_07_062243_create_location_user_table',
            'batch' => 1
        ],
    

        85 => [
            'id' => 86,
            'migration' => '2018_05_07_062243_create_locations_table',
            'batch' => 1
        ],
    

        86 => [
            'id' => 87,
            'migration' => '2018_05_07_062243_create_lv_activities_table',
            'batch' => 1
        ],
    

        87 => [
            'id' => 88,
            'migration' => '2018_05_07_062243_create_lv_activitymeta_table',
            'batch' => 1
        ],
    

        88 => [
            'id' => 89,
            'migration' => '2018_05_07_062243_create_lv_api_keys_table',
            'batch' => 1
        ],
    

        89 => [
            'id' => 90,
            'migration' => '2018_05_07_062243_create_lv_api_logs_table',
            'batch' => 1
        ],
    

        90 => [
            'id' => 91,
            'migration' => '2018_05_07_062243_create_lv_comments_table',
            'batch' => 1
        ],
    

        91 => [
            'id' => 92,
            'migration' => '2018_05_07_062243_create_lv_observationmeta_table',
            'batch' => 1
        ],
    

        92 => [
            'id' => 93,
            'migration' => '2018_05_07_062243_create_lv_observations_table',
            'batch' => 1
        ],
    

        93 => [
            'id' => 94,
            'migration' => '2018_05_07_062243_create_lv_page_timer_table',
            'batch' => 1
        ],
    

        94 => [
            'id' => 95,
            'migration' => '2018_05_07_062243_create_lv_password_resets_table',
            'batch' => 1
        ],
    

        95 => [
            'id' => 96,
            'migration' => '2018_05_07_062243_create_lv_permission_role_table',
            'batch' => 1
        ],
    

        96 => [
            'id' => 97,
            'migration' => '2018_05_07_062243_create_lv_permissions_table',
            'batch' => 1
        ],
    

        97 => [
            'id' => 98,
            'migration' => '2018_05_07_062243_create_lv_role_user_table',
            'batch' => 1
        ],
    

        98 => [
            'id' => 99,
            'migration' => '2018_05_07_062243_create_lv_roles_table',
            'batch' => 1
        ],
    

        99 => [
            'id' => 100,
            'migration' => '2018_05_07_062243_create_lv_rules_actions_table',
            'batch' => 1
        ],
    

        100 => [
            'id' => 101,
            'migration' => '2018_05_07_062243_create_lv_rules_conditions_table',
            'batch' => 1
        ],
    

        101 => [
            'id' => 102,
            'migration' => '2018_05_07_062243_create_lv_rules_intr_actions_table',
            'batch' => 1
        ],
    

        102 => [
            'id' => 103,
            'migration' => '2018_05_07_062243_create_lv_rules_intr_conditions_table',
            'batch' => 1
        ],
    

        103 => [
            'id' => 104,
            'migration' => '2018_05_07_062243_create_lv_rules_operators_table',
            'batch' => 1
        ],
    

        104 => [
            'id' => 105,
            'migration' => '2018_05_07_062243_create_lv_rules_table',
            'batch' => 1
        ],
    

        105 => [
            'id' => 106,
            'migration' => '2018_05_07_062243_create_lv_users_table',
            'batch' => 1
        ],
    

        106 => [
            'id' => 107,
            'migration' => '2018_05_07_062243_create_ma_fitbit_notifications_table',
            'batch' => 1
        ],
    

        107 => [
            'id' => 108,
            'migration' => '2018_05_07_062243_create_ma_resultmeta_table',
            'batch' => 1
        ],
    

        108 => [
            'id' => 109,
            'migration' => '2018_05_07_062243_create_ma_results_table',
            'batch' => 1
        ],
    

        109 => [
            'id' => 110,
            'migration' => '2018_05_07_062243_create_media_table',
            'batch' => 1
        ],
    

        110 => [
            'id' => 111,
            'migration' => '2018_05_07_062243_create_medication_groups_maps_table',
            'batch' => 1
        ],
    

        111 => [
            'id' => 112,
            'migration' => '2018_05_07_062243_create_medication_imports_table',
            'batch' => 1
        ],
    

        112 => [
            'id' => 113,
            'migration' => '2018_05_07_062243_create_notes_table',
            'batch' => 1
        ],
    

        113 => [
            'id' => 114,
            'migration' => '2018_05_07_062243_create_notifications_table',
            'batch' => 1
        ],
    

        114 => [
            'id' => 115,
            'migration' => '2018_05_07_062243_create_nurse_care_rate_logs_table',
            'batch' => 1
        ],
    

        115 => [
            'id' => 116,
            'migration' => '2018_05_07_062243_create_nurse_contact_window_table',
            'batch' => 1
        ],
    

        116 => [
            'id' => 117,
            'migration' => '2018_05_07_062243_create_nurse_info_state_table',
            'batch' => 1
        ],
    

        117 => [
            'id' => 118,
            'migration' => '2018_05_07_062243_create_nurse_info_table',
            'batch' => 1
        ],
    

        118 => [
            'id' => 119,
            'migration' => '2018_05_07_062243_create_nurse_monthly_summaries_table',
            'batch' => 1
        ],
    

        119 => [
            'id' => 120,
            'migration' => '2018_05_07_062243_create_patient_care_team_members_table',
            'batch' => 1
        ],
    

        120 => [
            'id' => 121,
            'migration' => '2018_05_07_062243_create_patient_contact_window_table',
            'batch' => 1
        ],
    

        121 => [
            'id' => 122,
            'migration' => '2018_05_07_062243_create_patient_info_table',
            'batch' => 1
        ],
    

        122 => [
            'id' => 123,
            'migration' => '2018_05_07_062243_create_patient_monthly_summaries_table',
            'batch' => 1
        ],
    

        123 => [
            'id' => 124,
            'migration' => '2018_05_07_062243_create_patient_reports_table',
            'batch' => 1
        ],
    

        124 => [
            'id' => 125,
            'migration' => '2018_05_07_062243_create_patient_sessions_table',
            'batch' => 1
        ],
    

        125 => [
            'id' => 126,
            'migration' => '2018_05_07_062243_create_patient_signups_table',
            'batch' => 1
        ],
    

        126 => [
            'id' => 127,
            'migration' => '2018_05_07_062243_create_pdfs_table',
            'batch' => 1
        ],
    

        127 => [
            'id' => 128,
            'migration' => '2018_05_07_062243_create_phoenix_heart_allergies_table',
            'batch' => 1
        ],
    

        128 => [
            'id' => 129,
            'migration' => '2018_05_07_062243_create_phoenix_heart_insurances_table',
            'batch' => 1
        ],
    

        129 => [
            'id' => 130,
            'migration' => '2018_05_07_062243_create_phoenix_heart_medications_table',
            'batch' => 1
        ],
    

        130 => [
            'id' => 131,
            'migration' => '2018_05_07_062243_create_phoenix_heart_names_table',
            'batch' => 1
        ],
    

        131 => [
            'id' => 132,
            'migration' => '2018_05_07_062243_create_phoenix_heart_problems_table',
            'batch' => 1
        ],
    

        132 => [
            'id' => 133,
            'migration' => '2018_05_07_062243_create_phone_numbers_table',
            'batch' => 1
        ],
    

        133 => [
            'id' => 134,
            'migration' => '2018_05_07_062243_create_practice_role_user_table',
            'batch' => 1
        ],
    

        134 => [
            'id' => 135,
            'migration' => '2018_05_07_062243_create_practices_table',
            'batch' => 1
        ],
    

        135 => [
            'id' => 136,
            'migration' => '2018_05_07_062243_create_problem_code_systems_table',
            'batch' => 1
        ],
    

        136 => [
            'id' => 137,
            'migration' => '2018_05_07_062243_create_problem_codes_table',
            'batch' => 1
        ],
    

        137 => [
            'id' => 138,
            'migration' => '2018_05_07_062243_create_problem_imports_table',
            'batch' => 1
        ],
    

        138 => [
            'id' => 139,
            'migration' => '2018_05_07_062243_create_processed_files_table',
            'batch' => 1
        ],
    

        139 => [
            'id' => 140,
            'migration' => '2018_05_07_062243_create_provider_info_table',
            'batch' => 1
        ],
    

        140 => [
            'id' => 141,
            'migration' => '2018_05_07_062243_create_q_a_import_summaries_table',
            'batch' => 1
        ],
    

        141 => [
            'id' => 142,
            'migration' => '2018_05_07_062243_create_revisions_table',
            'batch' => 1
        ],
    

        142 => [
            'id' => 143,
            'migration' => '2018_05_07_062243_create_rules_answers_table',
            'batch' => 1
        ],
    

        143 => [
            'id' => 144,
            'migration' => '2018_05_07_062243_create_rules_itemmeta_table',
            'batch' => 1
        ],
    

        144 => [
            'id' => 145,
            'migration' => '2018_05_07_062243_create_rules_items_table',
            'batch' => 1
        ],
    

        145 => [
            'id' => 146,
            'migration' => '2018_05_07_062243_create_rules_pcp_table',
            'batch' => 1
        ],
    

        146 => [
            'id' => 147,
            'migration' => '2018_05_07_062243_create_rules_question_sets_table',
            'batch' => 1
        ],
    

        147 => [
            'id' => 148,
            'migration' => '2018_05_07_062243_create_rules_questions_table',
            'batch' => 1
        ],
    

        148 => [
            'id' => 149,
            'migration' => '2018_05_07_062243_create_rules_ucp_table',
            'batch' => 1
        ],
    

        149 => [
            'id' => 150,
            'migration' => '2018_05_07_062243_create_saas_accounts_table',
            'batch' => 1
        ],
    

        150 => [
            'id' => 151,
            'migration' => '2018_05_07_062243_create_sessions_table',
            'batch' => 1
        ],
    

        151 => [
            'id' => 152,
            'migration' => '2018_05_07_062243_create_snomed_to_cpm_icd_maps_table',
            'batch' => 1
        ],
    

        152 => [
            'id' => 153,
            'migration' => '2018_05_07_062243_create_snomed_to_icd10_map_table',
            'batch' => 1
        ],
    

        153 => [
            'id' => 154,
            'migration' => '2018_05_07_062243_create_snomed_to_icd9_map_table',
            'batch' => 1
        ],
    

        154 => [
            'id' => 155,
            'migration' => '2018_05_07_062243_create_states_table',
            'batch' => 1
        ],
    

        155 => [
            'id' => 156,
            'migration' => '2018_05_07_062243_create_tabular_medical_records_table',
            'batch' => 1
        ],
    

        156 => [
            'id' => 157,
            'migration' => '2018_05_07_062243_create_target_patients_table',
            'batch' => 1
        ],
    

        157 => [
            'id' => 158,
            'migration' => '2018_05_07_062243_create_tz_countries_table',
            'batch' => 1
        ],
    

        158 => [
            'id' => 159,
            'migration' => '2018_05_07_062243_create_tz_timezones_table',
            'batch' => 1
        ],
    

        159 => [
            'id' => 160,
            'migration' => '2018_05_07_062243_create_tz_zones_table',
            'batch' => 1
        ],
    

        160 => [
            'id' => 161,
            'migration' => '2018_05_07_062243_create_usermeta_table',
            'batch' => 1
        ],
    

        161 => [
            'id' => 162,
            'migration' => '2018_05_07_062243_create_users_table',
            'batch' => 1
        ],
    

        162 => [
            'id' => 163,
            'migration' => '2018_05_07_062243_create_work_hours_table',
            'batch' => 1
        ],
    

        163 => [
            'id' => 164,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_addendums_table',
            'batch' => 1
        ],
    

        164 => [
            'id' => 165,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_allergy_imports_table',
            'batch' => 1
        ],
    

        165 => [
            'id' => 166,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_appointments_table',
            'batch' => 1
        ],
    

        166 => [
            'id' => 167,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_calls_table',
            'batch' => 1
        ],
    

        167 => [
            'id' => 168,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_ambassador_logs_table',
            'batch' => 1
        ],
    

        168 => [
            'id' => 169,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_ambassadors_table',
            'batch' => 1
        ],
    

        169 => [
            'id' => 170,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plan_templates_cpm_biometrics_table',
            'batch' => 1
        ],
    

        170 => [
            'id' => 171,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plan_templates_cpm_lifestyles_table',
            'batch' => 1
        ],
    

        171 => [
            'id' => 172,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plan_templates_cpm_medication_groups_table',
            'batch' => 1
        ],
    

        172 => [
            'id' => 173,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plan_templates_cpm_miscs_table',
            'batch' => 1
        ],
    

        173 => [
            'id' => 174,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plan_templates_cpm_problems_table',
            'batch' => 1
        ],
    

        174 => [
            'id' => 175,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plan_templates_cpm_symptoms_table',
            'batch' => 1
        ],
    

        175 => [
            'id' => 176,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plan_templates_table',
            'batch' => 1
        ],
    

        176 => [
            'id' => 177,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_care_plans_table',
            'batch' => 1
        ],
    

        177 => [
            'id' => 178,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_careplan_assessments_table',
            'batch' => 1
        ],
    

        178 => [
            'id' => 179,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_allergies_table',
            'batch' => 1
        ],
    

        179 => [
            'id' => 180,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_document_logs_table',
            'batch' => 1
        ],
    

        180 => [
            'id' => 181,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_insurance_policies_table',
            'batch' => 1
        ],
    

        181 => [
            'id' => 182,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_medications_table',
            'batch' => 1
        ],
    

        182 => [
            'id' => 183,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_problem_code_logs_table',
            'batch' => 1
        ],
    

        183 => [
            'id' => 184,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_problem_logs_table',
            'batch' => 1
        ],
    

        184 => [
            'id' => 185,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_problems_table',
            'batch' => 1
        ],
    

        185 => [
            'id' => 186,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_provider_logs_table',
            'batch' => 1
        ],
    

        186 => [
            'id' => 187,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccd_vendors_table',
            'batch' => 1
        ],
    

        187 => [
            'id' => 188,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccda_requests_table',
            'batch' => 1
        ],
    

        188 => [
            'id' => 189,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccdas_table',
            'batch' => 1
        ],
    

        189 => [
            'id' => 190,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_ccm_time_api_logs_table',
            'batch' => 1
        ],
    

        190 => [
            'id' => 191,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_contacts_table',
            'batch' => 1
        ],
    

        191 => [
            'id' => 192,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_biometrics_table',
            'batch' => 1
        ],
    

        192 => [
            'id' => 193,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_biometrics_users_table',
            'batch' => 1
        ],
    

        193 => [
            'id' => 194,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_blood_pressures_table',
            'batch' => 1
        ],
    

        194 => [
            'id' => 195,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_blood_sugars_table',
            'batch' => 1
        ],
    

        195 => [
            'id' => 196,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_lifestyles_table',
            'batch' => 1
        ],
    

        196 => [
            'id' => 197,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_lifestyles_users_table',
            'batch' => 1
        ],
    

        197 => [
            'id' => 198,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_medication_groups_table',
            'batch' => 1
        ],
    

        198 => [
            'id' => 199,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_medication_groups_users_table',
            'batch' => 1
        ],
    

        199 => [
            'id' => 200,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_miscs_table',
            'batch' => 1
        ],
    

        200 => [
            'id' => 201,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_miscs_users_table',
            'batch' => 1
        ],
    

        201 => [
            'id' => 202,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_problems_activate_cpm_biometrics_table',
            'batch' => 1
        ],
    

        202 => [
            'id' => 203,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_problems_activate_cpm_lifestyles_table',
            'batch' => 1
        ],
    

        203 => [
            'id' => 204,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_problems_activate_cpm_medication_groups_table',
            'batch' => 1
        ],
    

        204 => [
            'id' => 205,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_problems_activate_cpm_symptoms_table',
            'batch' => 1
        ],
    

        205 => [
            'id' => 206,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_problems_users_table',
            'batch' => 1
        ],
    

        206 => [
            'id' => 207,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_smokings_table',
            'batch' => 1
        ],
    

        207 => [
            'id' => 208,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_symptoms_table',
            'batch' => 1
        ],
    

        208 => [
            'id' => 209,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_symptoms_users_table',
            'batch' => 1
        ],
    

        209 => [
            'id' => 210,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_cpm_weights_table',
            'batch' => 1
        ],
    

        210 => [
            'id' => 211,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_demographics_imports_table',
            'batch' => 1
        ],
    

        211 => [
            'id' => 212,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_eligibility_batches_table',
            'batch' => 1
        ],
    

        212 => [
            'id' => 213,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_eligibility_jobs_table',
            'batch' => 1
        ],
    

        213 => [
            'id' => 214,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_email_settings_table',
            'batch' => 1
        ],
    

        214 => [
            'id' => 215,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_enrollees_table',
            'batch' => 1
        ],
    

        215 => [
            'id' => 216,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_foreign_ids_table',
            'batch' => 1
        ],
    

        216 => [
            'id' => 217,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_holidays_table',
            'batch' => 1
        ],
    

        217 => [
            'id' => 218,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_imported_medical_records_table',
            'batch' => 1
        ],
    

        218 => [
            'id' => 219,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_instructables_table',
            'batch' => 1
        ],
    

        219 => [
            'id' => 220,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_invites_table',
            'batch' => 1
        ],
    

        220 => [
            'id' => 221,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_location_user_table',
            'batch' => 1
        ],
    

        221 => [
            'id' => 222,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_locations_table',
            'batch' => 1
        ],
    

        222 => [
            'id' => 223,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_medication_groups_maps_table',
            'batch' => 1
        ],
    

        223 => [
            'id' => 224,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_medication_imports_table',
            'batch' => 1
        ],
    

        224 => [
            'id' => 225,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_notes_table',
            'batch' => 1
        ],
    

        225 => [
            'id' => 226,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_nurse_care_rate_logs_table',
            'batch' => 1
        ],
    

        226 => [
            'id' => 227,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_nurse_contact_window_table',
            'batch' => 1
        ],
    

        227 => [
            'id' => 228,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_nurse_info_state_table',
            'batch' => 1
        ],
    

        228 => [
            'id' => 229,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_nurse_info_table',
            'batch' => 1
        ],
    

        229 => [
            'id' => 230,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_nurse_monthly_summaries_table',
            'batch' => 1
        ],
    

        230 => [
            'id' => 231,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_patient_care_team_members_table',
            'batch' => 1
        ],
    

        231 => [
            'id' => 232,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_patient_contact_window_table',
            'batch' => 1
        ],
    

        232 => [
            'id' => 233,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_patient_info_table',
            'batch' => 1
        ],
    

        233 => [
            'id' => 234,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_patient_monthly_summaries_table',
            'batch' => 1
        ],
    

        234 => [
            'id' => 235,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_patient_reports_table',
            'batch' => 1
        ],
    

        235 => [
            'id' => 236,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_patient_sessions_table',
            'batch' => 1
        ],
    

        236 => [
            'id' => 237,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_pdfs_table',
            'batch' => 1
        ],
    

        237 => [
            'id' => 238,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_practice_role_user_table',
            'batch' => 1
        ],
    

        238 => [
            'id' => 239,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_practices_table',
            'batch' => 1
        ],
    

        239 => [
            'id' => 240,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_problem_codes_table',
            'batch' => 1
        ],
    

        240 => [
            'id' => 241,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_problem_imports_table',
            'batch' => 1
        ],
    

        241 => [
            'id' => 242,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_provider_info_table',
            'batch' => 1
        ],
    

        242 => [
            'id' => 243,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_q_a_import_summaries_table',
            'batch' => 1
        ],
    

        243 => [
            'id' => 244,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_snomed_to_cpm_icd_maps_table',
            'batch' => 1
        ],
    

        244 => [
            'id' => 245,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_snomed_to_icd9_map_table',
            'batch' => 1
        ],
    

        245 => [
            'id' => 246,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_tabular_medical_records_table',
            'batch' => 1
        ],
    

        246 => [
            'id' => 247,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_target_patients_table',
            'batch' => 1
        ],
    

        247 => [
            'id' => 248,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_tz_timezones_table',
            'batch' => 1
        ],
    

        248 => [
            'id' => 249,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_tz_zones_table',
            'batch' => 1
        ],
    

        249 => [
            'id' => 250,
            'migration' => '2018_05_07_062245_add_foreign_keys_to_users_table',
            'batch' => 1
        ]
    
        ]);
    }
}
