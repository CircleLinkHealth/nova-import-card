<?php

use Illuminate\Database\Seeder;

class MigrationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('migrations')->delete();

        \DB::table('migrations')->insert([
            0   =>
                [
                    'id'        => 1,
                    'migration' => '2016_11_11_164256_create_allergy_imports_table',
                    'batch'     => 0,
                ],
                1   =>
                [
                    'id'        => 2,
                    'migration' => '2016_11_11_164256_create_app_config_table',
                    'batch'     => 0,
                ],
                2   =>
                [
                    'id'        => 3,
                    'migration' => '2016_11_11_164256_create_calls_table',
                    'batch'     => 0,
                ],
                3   =>
                [
                    'id'        => 4,
                    'migration' => '2016_11_11_164256_create_care_items_table',
                    'batch'     => 0,
                ],
                4   =>
                [
                    'id'        => 5,
                    'migration' => '2016_11_11_164256_create_care_plan_templates_table',
                    'batch'     => 0,
                ],
                5   =>
                [
                    'id'        => 6,
                    'migration' => '2016_11_11_164256_create_care_plan_templates_cpm_biometrics_table',
                    'batch'     => 0,
                ],
                6   =>
                [
                    'id'        => 7,
                    'migration' => '2016_11_11_164256_create_care_plan_templates_cpm_lifestyles_table',
                    'batch'     => 0,
                ],
                7   =>
                [
                    'id'        => 8,
                    'migration' => '2016_11_11_164256_create_care_plan_templates_cpm_medication_groups_table',
                    'batch'     => 0,
                ],
                8   =>
                [
                    'id'        => 9,
                    'migration' => '2016_11_11_164256_create_care_plan_templates_cpm_miscs_table',
                    'batch'     => 0,
                ],
                9   =>
                [
                    'id'        => 10,
                    'migration' => '2016_11_11_164256_create_care_plan_templates_cpm_problems_table',
                    'batch'     => 0,
                ],
                10  =>
                [
                    'id'        => 11,
                    'migration' => '2016_11_11_164256_create_care_plan_templates_cpm_symptoms_table',
                    'batch'     => 0,
                ],
                11  =>
                [
                    'id'        => 12,
                    'migration' => '2016_11_11_164256_create_care_sections_table',
                    'batch'     => 0,
                ],
                12  =>
                [
                    'id'        => 13,
                    'migration' => '2016_11_11_164256_create_ccd_allergies_table',
                    'batch'     => 0,
                ],
                13  =>
                [
                    'id'        => 14,
                    'migration' => '2016_11_11_164256_create_ccd_allergy_logs_table',
                    'batch'     => 0,
                ],
                14  =>
                [
                    'id'        => 15,
                    'migration' => '2016_11_11_164256_create_ccd_demographics_logs_table',
                    'batch'     => 0,
                ],
                15  =>
                [
                    'id'        => 16,
                    'migration' => '2016_11_11_164256_create_ccd_document_logs_table',
                    'batch'     => 0,
                ],
                16  =>
                [
                    'id'        => 17,
                    'migration' => '2016_11_11_164256_create_ccd_import_routines_table',
                    'batch'     => 0,
                ],
                17  =>
                [
                    'id'        => 18,
                    'migration' => '2016_11_11_164256_create_ccd_import_routines_strategies_table',
                    'batch'     => 0,
                ],
                18  =>
                [
                    'id'        => 19,
                    'migration' => '2016_11_11_164256_create_ccd_insurance_policies_table',
                    'batch'     => 0,
                ],
                19  =>
                [
                    'id'        => 20,
                    'migration' => '2016_11_11_164256_create_ccd_medication_logs_table',
                    'batch'     => 0,
                ],
                20  =>
                [
                    'id'        => 21,
                    'migration' => '2016_11_11_164256_create_ccd_medications_table',
                    'batch'     => 0,
                ],
                21  =>
                [
                    'id'        => 22,
                    'migration' => '2016_11_11_164256_create_ccd_problem_logs_table',
                    'batch'     => 0,
                ],
                22  =>
                [
                    'id'        => 23,
                    'migration' => '2016_11_11_164256_create_ccd_problems_table',
                    'batch'     => 0,
                ],
                23  =>
                [
                    'id'        => 24,
                    'migration' => '2016_11_11_164256_create_ccd_provider_logs_table',
                    'batch'     => 0,
                ],
                24  =>
                [
                    'id'        => 25,
                    'migration' => '2016_11_11_164256_create_ccd_vendors_table',
                    'batch'     => 0,
                ],
                25  =>
                [
                    'id'        => 26,
                    'migration' => '2016_11_11_164256_create_ccda_requests_table',
                    'batch'     => 0,
                ],
                26  =>
                [
                    'id'        => 27,
                    'migration' => '2016_11_11_164256_create_ccdas_table',
                    'batch'     => 0,
                ],
                27  =>
                [
                    'id'        => 28,
                    'migration' => '2016_11_11_164256_create_ccm_time_api_logs_table',
                    'batch'     => 0,
                ],
                28  =>
                [
                    'id'        => 29,
                    'migration' => '2016_11_11_164256_create_cpm_biometrics_table',
                    'batch'     => 0,
                ],
                29  =>
                [
                    'id'        => 30,
                    'migration' => '2016_11_11_164256_create_cpm_biometrics_users_table',
                    'batch'     => 0,
                ],
                30  =>
                [
                    'id'        => 31,
                    'migration' => '2016_11_11_164256_create_cpm_blood_pressures_table',
                    'batch'     => 0,
                ],
                31  =>
                [
                    'id'        => 32,
                    'migration' => '2016_11_11_164256_create_cpm_blood_sugars_table',
                    'batch'     => 0,
                ],
                32  =>
                [
                    'id'        => 33,
                    'migration' => '2016_11_11_164256_create_cpm_instructions_table',
                    'batch'     => 0,
                ],
                33  =>
                [
                    'id'        => 34,
                    'migration' => '2016_11_11_164256_create_cpm_lifestyles_table',
                    'batch'     => 0,
                ],
                34  =>
                [
                    'id'        => 35,
                    'migration' => '2016_11_11_164256_create_cpm_lifestyles_users_table',
                    'batch'     => 0,
                ],
                35  =>
                [
                    'id'        => 36,
                    'migration' => '2016_11_11_164256_create_cpm_mail_logs_table',
                    'batch'     => 0,
                ],
                36  =>
                [
                    'id'        => 37,
                    'migration' => '2016_11_11_164256_create_cpm_medication_groups_table',
                    'batch'     => 0,
                ],
                37  =>
                [
                    'id'        => 38,
                    'migration' => '2016_11_11_164256_create_cpm_medication_groups_users_table',
                    'batch'     => 0,
                ],
                38  =>
                [
                    'id'        => 39,
                    'migration' => '2016_11_11_164256_create_cpm_miscs_table',
                    'batch'     => 0,
                ],
                39  =>
                [
                    'id'        => 40,
                    'migration' => '2016_11_11_164256_create_cpm_miscs_users_table',
                    'batch'     => 0,
                ],
                40  =>
                [
                    'id'        => 41,
                    'migration' => '2016_11_11_164256_create_cpm_problems_table',
                    'batch'     => 0,
                ],
                41  =>
                [
                    'id'        => 42,
                    'migration' => '2016_11_11_164256_create_cpm_problems_activate_cpm_biometrics_table',
                    'batch'     => 0,
                ],
                42  =>
                [
                    'id'        => 43,
                    'migration' => '2016_11_11_164256_create_cpm_problems_activate_cpm_lifestyles_table',
                    'batch'     => 0,
                ],
                43  =>
                [
                    'id'        => 44,
                    'migration' => '2016_11_11_164256_create_cpm_problems_activate_cpm_medication_groups_table',
                    'batch'     => 0,
                ],
                44  =>
                [
                    'id'        => 45,
                    'migration' => '2016_11_11_164256_create_cpm_problems_activate_cpm_symptoms_table',
                    'batch'     => 0,
                ],
                45  =>
                [
                    'id'        => 46,
                    'migration' => '2016_11_11_164256_create_cpm_problems_users_table',
                    'batch'     => 0,
                ],
                46  =>
                [
                    'id'        => 47,
                    'migration' => '2016_11_11_164256_create_cpm_smokings_table',
                    'batch'     => 0,
                ],
                47  =>
                [
                    'id'        => 48,
                    'migration' => '2016_11_11_164256_create_cpm_symptoms_table',
                    'batch'     => 0,
                ],
                48  =>
                [
                    'id'        => 49,
                    'migration' => '2016_11_11_164256_create_cpm_symptoms_users_table',
                    'batch'     => 0,
                ],
                49  =>
                [
                    'id'        => 50,
                    'migration' => '2016_11_11_164256_create_cpm_weights_table',
                    'batch'     => 0,
                ],
                50  =>
                [
                    'id'        => 51,
                    'migration' => '2016_11_11_164256_create_demographics_imports_table',
                    'batch'     => 0,
                ],
                51  =>
                [
                    'id'        => 52,
                    'migration' => '2016_11_11_164256_create_email_settings_table',
                    'batch'     => 0,
                ],
                52  =>
                [
                    'id'        => 53,
                    'migration' => '2016_11_11_164256_create_families_table',
                    'batch'     => 0,
                ],
                53  =>
                [
                    'id'        => 54,
                    'migration' => '2016_11_11_164256_create_foreign_ids_table',
                    'batch'     => 0,
                ],
                54  =>
                [
                    'id'        => 55,
                    'migration' => '2016_11_11_164256_create_instructables_table',
                    'batch'     => 0,
                ],
                55  =>
                [
                    'id'        => 56,
                    'migration' => '2016_11_11_164256_create_location_user_table',
                    'batch'     => 0,
                ],
                56  =>
                [
                    'id'        => 57,
                    'migration' => '2016_11_11_164256_create_locations_table',
                    'batch'     => 0,
                ],
                57  =>
                [
                    'id'        => 58,
                    'migration' => '2016_11_11_164256_create_lv_activities_table',
                    'batch'     => 0,
                ],
                58  =>
                [
                    'id'        => 59,
                    'migration' => '2016_11_11_164256_create_lv_activitymeta_table',
                    'batch'     => 0,
                ],
                59  =>
                [
                    'id'        => 60,
                    'migration' => '2016_11_11_164256_create_lv_api_keys_table',
                    'batch'     => 0,
                ],
                60  =>
                [
                    'id'        => 61,
                    'migration' => '2016_11_11_164256_create_lv_api_logs_table',
                    'batch'     => 0,
                ],
                61  =>
                [
                    'id'        => 62,
                    'migration' => '2016_11_11_164256_create_lv_comments_table',
                    'batch'     => 0,
                ],
                62  =>
                [
                    'id'        => 63,
                    'migration' => '2016_11_11_164256_create_lv_migrations_table',
                    'batch'     => 0,
                ],
                63  =>
                [
                    'id'        => 64,
                    'migration' => '2016_11_11_164256_create_lv_observationmeta_table',
                    'batch'     => 0,
                ],
                64  =>
                [
                    'id'        => 65,
                    'migration' => '2016_11_11_164256_create_lv_observations_table',
                    'batch'     => 0,
                ],
                65  =>
                [
                    'id'        => 66,
                    'migration' => '2016_11_11_164256_create_lv_page_timer_table',
                    'batch'     => 0,
                ],
                66  =>
                [
                    'id'        => 67,
                    'migration' => '2016_11_11_164256_create_lv_password_resets_table',
                    'batch'     => 0,
                ],
                67  =>
                [
                    'id'        => 68,
                    'migration' => '2016_11_11_164256_create_lv_permission_role_table',
                    'batch'     => 0,
                ],
                68  =>
                [
                    'id'        => 69,
                    'migration' => '2016_11_11_164256_create_lv_permissions_table',
                    'batch'     => 0,
                ],
                69  =>
                [
                    'id'        => 70,
                    'migration' => '2016_11_11_164256_create_lv_role_user_table',
                    'batch'     => 0,
                ],
                70  =>
                [
                    'id'        => 71,
                    'migration' => '2016_11_11_164256_create_lv_roles_table',
                    'batch'     => 0,
                ],
                71  =>
                [
                    'id'        => 72,
                    'migration' => '2016_11_11_164256_create_lv_rules_table',
                    'batch'     => 0,
                ],
                72  =>
                [
                    'id'        => 73,
                    'migration' => '2016_11_11_164256_create_lv_rules_actions_table',
                    'batch'     => 0,
                ],
                73  =>
                [
                    'id'        => 74,
                    'migration' => '2016_11_11_164256_create_lv_rules_conditions_table',
                    'batch'     => 0,
                ],
                74  =>
                [
                    'id'        => 75,
                    'migration' => '2016_11_11_164256_create_lv_rules_intr_actions_table',
                    'batch'     => 0,
                ],
                75  =>
                [
                    'id'        => 76,
                    'migration' => '2016_11_11_164256_create_lv_rules_intr_conditions_table',
                    'batch'     => 0,
                ],
                76  =>
                [
                    'id'        => 77,
                    'migration' => '2016_11_11_164256_create_lv_rules_operators_table',
                    'batch'     => 0,
                ],
                77  =>
                [
                    'id'        => 78,
                    'migration' => '2016_11_11_164256_create_medication_imports_table',
                    'batch'     => 0,
                ],
                78  =>
                [
                    'id'        => 79,
                    'migration' => '2016_11_11_164256_create_notes_table',
                    'batch'     => 0,
                ],
                79  =>
                [
                    'id'        => 80,
                    'migration' => '2016_11_11_164256_create_nurse_contact_window_table',
                    'batch'     => 0,
                ],
                80  =>
                [
                    'id'        => 81,
                    'migration' => '2016_11_11_164256_create_nurse_info_table',
                    'batch'     => 0,
                ],
                81  =>
                [
                    'id'        => 82,
                    'migration' => '2016_11_11_164256_create_nurse_info_state_table',
                    'batch'     => 0,
                ],
                82  =>
                [
                    'id'        => 83,
                    'migration' => '2016_11_11_164256_create_nurse_monthly_summaries_table',
                    'batch'     => 0,
                ],
                83  =>
                [
                    'id'        => 84,
                    'migration' => '2016_11_11_164256_create_patient_care_plans_table',
                    'batch'     => 0,
                ],
                84  =>
                [
                    'id'        => 85,
                    'migration' => '2016_11_11_164256_create_patient_care_team_members_table',
                    'batch'     => 0,
                ],
                85  =>
                [
                    'id'        => 86,
                    'migration' => '2016_11_11_164256_create_patient_contact_window_table',
                    'batch'     => 0,
                ],
                86  =>
                [
                    'id'        => 87,
                    'migration' => '2016_11_11_164256_create_patient_info_table',
                    'batch'     => 0,
                ],
                87  =>
                [
                    'id'        => 88,
                    'migration' => '2016_11_11_164256_create_patient_monthly_summaries_table',
                    'batch'     => 0,
                ],
                88  =>
                [
                    'id'        => 89,
                    'migration' => '2016_11_11_164256_create_patient_reports_table',
                    'batch'     => 0,
                ],
                89  =>
                [
                    'id'        => 90,
                    'migration' => '2016_11_11_164256_create_patient_sessions_table',
                    'batch'     => 0,
                ],
                90  =>
                [
                    'id'        => 91,
                    'migration' => '2016_11_11_164256_create_phone_numbers_table',
                    'batch'     => 0,
                ],
                91  =>
                [
                    'id'        => 92,
                    'migration' => '2016_11_11_164256_create_practice_user_table',
                    'batch'     => 0,
                ],
                92  =>
                [
                    'id'        => 93,
                    'migration' => '2016_11_11_164256_create_practices_table',
                    'batch'     => 0,
                ],
                93  =>
                [
                    'id'        => 94,
                    'migration' => '2016_11_11_164256_create_problem_imports_table',
                    'batch'     => 0,
                ],
                94  =>
                [
                    'id'        => 95,
                    'migration' => '2016_11_11_164256_create_provider_info_table',
                    'batch'     => 0,
                ],
                95  =>
                [
                    'id'        => 96,
                    'migration' => '2016_11_11_164256_create_q_a_import_summaries_table',
                    'batch'     => 0,
                ],
                96  =>
                [
                    'id'        => 97,
                    'migration' => '2016_11_11_164256_create_revisions_table',
                    'batch'     => 0,
                ],
                97  =>
                [
                    'id'        => 98,
                    'migration' => '2016_11_11_164256_create_rules_answers_table',
                    'batch'     => 0,
                ],
                98  =>
                [
                    'id'        => 99,
                    'migration' => '2016_11_11_164256_create_rules_itemmeta_table',
                    'batch'     => 0,
                ],
                99  =>
                [
                    'id'        => 100,
                    'migration' => '2016_11_11_164256_create_rules_items_table',
                    'batch'     => 0,
                ],
                100 =>
                [
                    'id'        => 101,
                    'migration' => '2016_11_11_164256_create_rules_pcp_table',
                    'batch'     => 0,
                ],
                101 =>
                [
                    'id'        => 102,
                    'migration' => '2016_11_11_164256_create_rules_question_sets_table',
                    'batch'     => 0,
                ],
                102 =>
                [
                    'id'        => 103,
                    'migration' => '2016_11_11_164256_create_rules_questions_table',
                    'batch'     => 0,
                ],
                103 =>
                [
                    'id'        => 104,
                    'migration' => '2016_11_11_164256_create_rules_ucp_table',
                    'batch'     => 0,
                ],
                104 =>
                [
                    'id'        => 105,
                    'migration' => '2016_11_11_164256_create_sessions_table',
                    'batch'     => 0,
                ],
                105 =>
                [
                    'id'        => 106,
                    'migration' => '2016_11_11_164256_create_snomed_to_cpm_icd_maps_table',
                    'batch'     => 0,
                ],
                106 =>
                [
                    'id'        => 107,
                    'migration' => '2016_11_11_164256_create_snomed_to_icd10_map_table',
                    'batch'     => 0,
                ],
                107 =>
                [
                    'id'        => 108,
                    'migration' => '2016_11_11_164256_create_states_table',
                    'batch'     => 0,
                ],
                108 =>
                [
                    'id'        => 109,
                    'migration' => '2016_11_11_164256_create_usermeta_table',
                    'batch'     => 0,
                ],
                109 =>
                [
                    'id'        => 110,
                    'migration' => '2016_11_11_164256_create_users_table',
                    'batch'     => 0,
                ],
                110 =>
                [
                    'id'        => 111,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_allergy_imports_table',
                    'batch'     => 0,
                ],
                111 =>
                [
                    'id'        => 112,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_calls_table',
                    'batch'     => 0,
                ],
                112 =>
                [
                    'id'        => 113,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_care_plan_templates_table',
                    'batch'     => 0,
                ],
                113 =>
                [
                    'id'        => 114,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_care_plan_templates_cpm_biometrics_table',
                    'batch'     => 0,
                ],
                114 =>
                [
                    'id'        => 115,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_care_plan_templates_cpm_lifestyles_table',
                    'batch'     => 0,
                ],
                115 =>
                [
                    'id'        => 116,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_care_plan_templates_cpm_medication_groups_table',
                    'batch'     => 0,
                ],
                116 =>
                [
                    'id'        => 117,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_care_plan_templates_cpm_miscs_table',
                    'batch'     => 0,
                ],
                117 =>
                [
                    'id'        => 118,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_care_plan_templates_cpm_problems_table',
                    'batch'     => 0,
                ],
                118 =>
                [
                    'id'        => 119,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_care_plan_templates_cpm_symptoms_table',
                    'batch'     => 0,
                ],
                119 =>
                [
                    'id'        => 120,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccd_allergies_table',
                    'batch'     => 0,
                ],
                120 =>
                [
                    'id'        => 121,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccd_insurance_policies_table',
                    'batch'     => 0,
                ],
                121 =>
                [
                    'id'        => 122,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccd_medications_table',
                    'batch'     => 0,
                ],
                122 =>
                [
                    'id'        => 123,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccd_problem_logs_table',
                    'batch'     => 0,
                ],
                123 =>
                [
                    'id'        => 124,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccd_problems_table',
                    'batch'     => 0,
                ],
                124 =>
                [
                    'id'        => 125,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccd_vendors_table',
                    'batch'     => 0,
                ],
                125 =>
                [
                    'id'        => 126,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccda_requests_table',
                    'batch'     => 0,
                ],
                126 =>
                [
                    'id'        => 127,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccdas_table',
                    'batch'     => 0,
                ],
                127 =>
                [
                    'id'        => 128,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_ccm_time_api_logs_table',
                    'batch'     => 0,
                ],
                128 =>
                [
                    'id'        => 129,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_biometrics_table',
                    'batch'     => 0,
                ],
                129 =>
                [
                    'id'        => 130,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_biometrics_users_table',
                    'batch'     => 0,
                ],
                130 =>
                [
                    'id'        => 131,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_blood_pressures_table',
                    'batch'     => 0,
                ],
                131 =>
                [
                    'id'        => 132,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_blood_sugars_table',
                    'batch'     => 0,
                ],
                132 =>
                [
                    'id'        => 133,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_lifestyles_table',
                    'batch'     => 0,
                ],
                133 =>
                [
                    'id'        => 134,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_lifestyles_users_table',
                    'batch'     => 0,
                ],
                134 =>
                [
                    'id'        => 135,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_mail_logs_table',
                    'batch'     => 0,
                ],
                135 =>
                [
                    'id'        => 136,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_medication_groups_table',
                    'batch'     => 0,
                ],
                136 =>
                [
                    'id'        => 137,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_medication_groups_users_table',
                    'batch'     => 0,
                ],
                137 =>
                [
                    'id'        => 138,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_miscs_table',
                    'batch'     => 0,
                ],
                138 =>
                [
                    'id'        => 139,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_miscs_users_table',
                    'batch'     => 0,
                ],
                139 =>
                [
                    'id'        => 140,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_problems_activate_cpm_biometrics_table',
                    'batch'     => 0,
                ],
                140 =>
                [
                    'id'        => 141,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_problems_activate_cpm_lifestyles_table',
                    'batch'     => 0,
                ],
                141 =>
                [
                    'id'        => 142,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_problems_activate_cpm_medication_groups_table',
                    'batch'     => 0,
                ],
                142 =>
                [
                    'id'        => 143,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_problems_activate_cpm_symptoms_table',
                    'batch'     => 0,
                ],
                143 =>
                [
                    'id'        => 144,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_problems_users_table',
                    'batch'     => 0,
                ],
                144 =>
                [
                    'id'        => 145,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_smokings_table',
                    'batch'     => 0,
                ],
                145 =>
                [
                    'id'        => 146,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_symptoms_table',
                    'batch'     => 0,
                ],
                146 =>
                [
                    'id'        => 147,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_symptoms_users_table',
                    'batch'     => 0,
                ],
                147 =>
                [
                    'id'        => 148,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_cpm_weights_table',
                    'batch'     => 0,
                ],
                148 =>
                [
                    'id'        => 149,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_demographics_imports_table',
                    'batch'     => 0,
                ],
                149 =>
                [
                    'id'        => 150,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_email_settings_table',
                    'batch'     => 0,
                ],
                150 =>
                [
                    'id'        => 151,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_foreign_ids_table',
                    'batch'     => 0,
                ],
                151 =>
                [
                    'id'        => 152,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_instructables_table',
                    'batch'     => 0,
                ],
                152 =>
                [
                    'id'        => 153,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_location_user_table',
                    'batch'     => 0,
                ],
                153 =>
                [
                    'id'        => 154,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_locations_table',
                    'batch'     => 0,
                ],
                154 =>
                [
                    'id'        => 155,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_medication_imports_table',
                    'batch'     => 0,
                ],
                155 =>
                [
                    'id'        => 156,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_notes_table',
                    'batch'     => 0,
                ],
                156 =>
                [
                    'id'        => 157,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_nurse_contact_window_table',
                    'batch'     => 0,
                ],
                157 =>
                [
                    'id'        => 158,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_nurse_info_table',
                    'batch'     => 0,
                ],
                158 =>
                [
                    'id'        => 159,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_nurse_info_state_table',
                    'batch'     => 0,
                ],
                159 =>
                [
                    'id'        => 160,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_nurse_monthly_summaries_table',
                    'batch'     => 0,
                ],
                160 =>
                [
                    'id'        => 161,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_patient_care_plans_table',
                    'batch'     => 0,
                ],
                161 =>
                [
                    'id'        => 162,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_patient_care_team_members_table',
                    'batch'     => 0,
                ],
                162 =>
                [
                    'id'        => 163,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_patient_contact_window_table',
                    'batch'     => 0,
                ],
                163 =>
                [
                    'id'        => 164,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_patient_info_table',
                    'batch'     => 0,
                ],
                164 =>
                [
                    'id'        => 165,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_patient_monthly_summaries_table',
                    'batch'     => 0,
                ],
                165 =>
                [
                    'id'        => 166,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_patient_reports_table',
                    'batch'     => 0,
                ],
                166 =>
                [
                    'id'        => 167,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_patient_sessions_table',
                    'batch'     => 0,
                ],
                167 =>
                [
                    'id'        => 168,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_practice_user_table',
                    'batch'     => 0,
                ],
                168 =>
                [
                    'id'        => 169,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_practices_table',
                    'batch'     => 0,
                ],
                169 =>
                [
                    'id'        => 170,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_problem_imports_table',
                    'batch'     => 0,
                ],
                170 =>
                [
                    'id'        => 171,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_provider_info_table',
                    'batch'     => 0,
                ],
                171 =>
                [
                    'id'        => 172,
                    'migration' => '2016_11_11_164301_add_foreign_keys_to_q_a_import_summaries_table',
                    'batch'     => 0,
                ],
        ]);
    }
}
