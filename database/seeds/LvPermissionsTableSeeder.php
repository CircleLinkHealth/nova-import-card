<?php

use App\Permission;
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
        foreach ($this->perms() as $perm) {
            Permission::updateOrCreate([
                'name' => $perm['name'],
            ], $perm);
        }
    }

    public function perms()
    {
        return [
            [
                'name'         => 'users-view-all',
                'display_name' => 'Users - View All',
            ],
            [
                'name'         => 'users-view-self',
                'display_name' => 'Users - view self',
            ],
            [
                'name'         => 'activities-manage',
                'display_name' => 'Activities Manage',
            ],
            [
                'name'         => 'activities-view',
                'display_name' => 'Activities View',
            ],
            [
                'name'         => 'activities-pagetimer-manage',
                'display_name' => 'Time Tracking Manage',
            ],
            [
                'name'         => 'activities-pagetimer-view',
                'display_name' => 'Time Tracking View',
            ],
            [
                'name'         => 'admin-access',
                'display_name' => 'Admin Access',
            ],
            [
                'name'         => 'apikeys-manage',
                'display_name' => 'API Manage',
            ],
            [
                'name'         => 'apikeys-view',
                'display_name' => 'API View',
            ],
            [
                'name'         => 'locations-manage',
                'display_name' => 'Locations Manage',
            ],
            [
                'name'         => 'locations-view',
                'display_name' => 'Locations View',
            ],
            [
                'name'         => 'observations-create',
                'display_name' => 'Observations Create',
            ],
            [
                'name'         => 'observations-destroy',
                'display_name' => 'Observations Destroy',
            ],
            [
                'name'         => 'observations-edit',
                'display_name' => 'Observations Edit',
            ],
            [
                'name'         => 'observations-view',
                'display_name' => 'Observations View',
            ],
            [
                'name'         => 'programs-manage',
                'display_name' => 'Programs Manage',
            ],
            [
                'name'         => 'programs-view',
                'display_name' => 'Programs View',
            ],
            [
                'name'         => 'roles-manage',
                'display_name' => 'Roles Manage',
            ],
            [
                'name'         => 'roles-view',
                'display_name' => 'Roles View',
            ],
            [
                'name'         => 'roles-permissions-manage',
                'display_name' => 'Roles Permissions Manage',
            ],
            [
                'name'         => 'roles-permissions-view',
                'display_name' => 'Roles Permissions View',
            ],
            [
                'name'         => 'rules-engine-manage',
                'display_name' => 'Rules Engine Manage',
            ],
            [
                'name'         => 'rules-engine-view',
                'display_name' => 'Rules Engine View',
            ],
            [
                'name'         => 'users-create',
                'display_name' => 'User Create New User',
            ],
            [
                'name'         => 'users-edit-all',
                'display_name' => 'User Edit All',
                'description'  => '',
                'created_at'   => '2016-03-28 21:03:49',
                'updated_at'   => '2016-03-28 21:03:49',
            ],
            [
                'name'         => 'users-edit-self',
                'display_name' => 'User Edit Self',
            ],
            [
                'name'         => 'post-ccd-to-api',
                'display_name' => 'POST CCDs to API',
                'description'  => 'Can POST CCDs to our API.',
            ],
            [
                'name'         => 'query-api-for-patient-data',
                'display_name' => 'Query API for Patient Data',
                'description'  => 'Can POST CCDs to our API.',
            ],
            [
                'name'         => 'ccd-import',
                'display_name' => 'Import CCDs',
                'description'  => 'Can use the CCD Importer.',
            ],
            [
                'name'         => 'app-config-manage',
                'display_name' => 'App Config Manage',
            ],
            [
                'name'         => 'app-config-view',
                'display_name' => 'App Config View',
            ],
            [
                'name'         => 'practice-manage',
                'display_name' => 'Practice Manage',
                'description'  => 'Can Update or Delete a Practice.',
            ],
            [
                'name'         => 'use-onboarding',
                'display_name' => 'Use Onboarding without a code',
                'description'  => 'Can use Onboarding to set up a Practice.',
            ],
            [
                'name'         => 'care-plan-approve',
                'display_name' => 'Approve Careplans',
                'description'  => 'Can approve CarePlans with status qa_approved. Changes the CarePlan status to provider_approved.',
            ],
            [
                'name'         => 'care-plan-qa-approve',
                'display_name' => 'CLH Approve Careplan',
                'description'  => 'Can approve CarePlans with status draft. Changes the CarePlan status to qa_approved.',
            ],
            [
                'name'         => 'read-practice-chargeable-service',
                'display_name' => 'View the ChargeableServices for a Practice.',
                'description'  => 'Can View the ChargeableServices for a Practice.',
            ],
            [
                'name'         => 'create-practice-chargeable-service',
                'display_name' => 'Create a ChargeableService for a Practice.',
                'description'  => 'Can Create ChargeableServices for a Practice.',
            ],
            [
                'name'         => 'delete-practice-chargeable-service',
                'display_name' => 'Delete a ChargeableService for a Practice.',
                'description'  => 'Can Delete ChargeableServices for a Practice.',
            ],
            [
                'name'         => 'update-practice-chargeable-service',
                'display_name' => 'Update a ChargeableService for a Practice.',
                'description'  => 'Can Update ChargeableServices for a Practice.',
            ],
        ];
    }
}