<?php

use Illuminate\Database\Seeder;

class LvPermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('lv_permissions')->delete();
        
        \DB::table('lv_permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'users-view-all',
                'display_name' => 'Users - View All',
                'description' => '',
                'created_at' => '2015-11-17 14:54:26',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'users-view-self',
                'display_name' => 'Users - view self',
                'description' => '',
                'created_at' => '2015-11-17 14:55:59',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'activities-manage',
                'display_name' => 'Activities Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'activities-view',
                'display_name' => 'Activities View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'activities-pagetimer-manage',
                'display_name' => 'Time Tracking Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'activities-pagetimer-view',
                'display_name' => 'Time Tracking View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'admin-access',
                'display_name' => 'Admin Access',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'apikeys-manage',
                'display_name' => 'API Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'apikeys-view',
                'display_name' => 'API View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'locations-manage',
                'display_name' => 'Locations Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'locations-view',
                'display_name' => 'Locations View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'observations-create',
                'display_name' => 'Observations Create',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'observations-destroy',
                'display_name' => 'Observations Destroy',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'observations-edit',
                'display_name' => 'Observations Edit',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'observations-view',
                'display_name' => 'Observations View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'programs-manage',
                'display_name' => 'Programs Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'programs-view',
                'display_name' => 'Programs View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'roles-manage',
                'display_name' => 'Roles Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'roles-view',
                'display_name' => 'Roles View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'roles-permissions-manage',
                'display_name' => 'Roles Permissions Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'roles-permissions-view',
                'display_name' => 'Roles Permissions View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'rules-engine-manage',
                'display_name' => 'Rules Engine Manage',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'rules-engine-view',
                'display_name' => 'Rules Engine View',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'users-create',
                'display_name' => 'User Create New User',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'users-edit-all',
                'display_name' => 'User Edit All',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'users-edit-self',
                'display_name' => 'User Edit Self',
                'description' => '',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'post-ccd-to-api',
                'display_name' => 'POST CCDs to API',
                'description' => 'Can POST CCDs to our API.',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'query-api-for-patient-data',
                'display_name' => 'Query API for Patient Data',
                'description' => 'Can POST CCDs to our API.',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'ccd-import',
                'display_name' => 'Import CCDs',
                'description' => 'Can use the CCD Importer.',
                'created_at' => '2016-03-28 21:03:49',
                'updated_at' => '2016-03-28 21:03:49',
            ),
            29 => 
            array (
                'id' => 30,
                'name' => 'app-config-manage',
                'display_name' => 'App Config Manage',
                'description' => '',
                'created_at' => '2016-05-27 23:49:05',
                'updated_at' => '2016-05-27 23:49:05',
            ),
            30 => 
            array (
                'id' => 31,
                'name' => 'app-config-view',
                'display_name' => 'App Config View',
                'description' => '',
                'created_at' => '2016-05-27 23:49:05',
                'updated_at' => '2016-05-27 23:49:05',
            ),
            31 => 
            array (
                'id' => 32,
                'name' => 'practice-manage',
                'display_name' => 'Practice Manage',
                'description' => 'Can Update or Delete a Practice.',
                'created_at' => '2016-12-15 21:48:43',
                'updated_at' => '2016-12-15 21:48:43',
            ),
            32 => 
            array (
                'id' => 33,
                'name' => 'use-onboarding',
                'display_name' => 'Use Onboarding without a code',
                'description' => 'Can use Onboarding to set up a Practice.',
                'created_at' => '2017-01-12 13:20:34',
                'updated_at' => '2017-01-12 13:20:34',
            ),
            33 => 
            array (
                'id' => 34,
                'name' => 'care-plan-approve',
                'display_name' => 'Approve Careplans',
                'description' => 'Can approve CarePlans with status qa_approved. Changes the CarePlan status to provider_approved.',
                'created_at' => '2017-06-15 19:03:16',
                'updated_at' => '2017-06-15 19:03:16',
            ),
            34 => 
            array (
                'id' => 35,
                'name' => 'care-plan-qa-approve',
                'display_name' => 'CLH Approve Careplan',
                'description' => 'Can approve CarePlans with status draft. Changes the CarePlan status to qa_approved.',
                'created_at' => '2017-06-15 19:03:16',
                'updated_at' => '2017-06-15 19:03:16',
            ),
            35 => 
            array (
                'id' => 36,
                'name' => 'read-practice-chargeable-service',
                'display_name' => 'View the ChargeableServices for a Practice.',
                'description' => 'Can View the ChargeableServices for a Practice.',
                'created_at' => '2018-03-09 06:18:43',
                'updated_at' => '2018-03-09 06:18:43',
            ),
            36 => 
            array (
                'id' => 37,
                'name' => 'create-practice-chargeable-service',
                'display_name' => 'Create a ChargeableService for a Practice.',
                'description' => 'Can Create ChargeableServices for a Practice.',
                'created_at' => '2018-03-09 06:18:43',
                'updated_at' => '2018-03-09 06:18:43',
            ),
            37 => 
            array (
                'id' => 38,
                'name' => 'delete-practice-chargeable-service',
                'display_name' => 'Delete a ChargeableService for a Practice.',
                'description' => 'Can Delete ChargeableServices for a Practice.',
                'created_at' => '2018-03-09 06:18:43',
                'updated_at' => '2018-03-09 06:18:43',
            ),
            38 => 
            array (
                'id' => 39,
                'name' => 'update-practice-chargeable-service',
                'display_name' => 'Update a ChargeableService for a Practice.',
                'description' => 'Can Update ChargeableServices for a Practice.',
                'created_at' => '2018-03-09 06:18:43',
                'updated_at' => '2018-03-09 06:18:43',
            ),
        ));
        
        
    }
}