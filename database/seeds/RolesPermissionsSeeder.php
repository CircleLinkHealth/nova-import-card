<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('lv_roles')->delete();

        $this->call(LvPermissionsTableSeeder::class);

        foreach ($this->roles() as $role) {
            $permissionsArr = $role['permissions'];

            unset($role['permissions']);

            $role = Role::updateOrCreate($role);

            $permissionIds = Permission::whereIn('name', $permissionsArr)
                ->pluck('id')->all();

            $role->perms()->sync($permissionIds);

            $name = $role['name'];
            $this->command->info("role $name created");
        }

        $this->giveAdminsAllPermissions('administrator');
        $this->giveAdminsAllPermissions('saas-admin');

        $this->command->info('all roles and permissions created');
    }

    public function roles()
    {
        return [
            [
                'name' => 'administrator',
                'display_name' => 'Administrator',
                'description' => 'Administrator',
                'permissions' => [ ]
            ],
            [
                'name' => 'participant',
                'display_name' => 'Participant',
                'description' => 'Participant',
                'permissions' => [ ]
            ],
            [
                'name' => 'api-ccd-vendor',
                'display_name' => 'API CCD Vendor',
                'description' => 'Is able to post CCDs to our API',
                'permissions' => [ ]
            ],
            [
                'name' => 'api-data-consumer',
                'display_name' => 'API Data Consumer',
                'description' => 'Is able to receive PDF Reports and CCM Time from our API',
                'permissions' => [ ]
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => '',
                'permissions' => [ ]
            ],
            [
                'name' => 'aprima-api-location',
                'display_name' => 'API Data Consumer and CCD Vendor.',
                'description' => 'This role is JUST FOR APRIMA! Is able to receive PDF Reports and CCM Time from our API. Is able to post CCDs to our API.',
                'permissions' => [ ]
            ],
            [
                'name' => 'no-ccm-care-center',
                'display_name' => 'Non CCM Care Center',
                'description' => 'Care Center',
                'permissions' => [ ]
            ],
            [
                'name' => 'no-access',
                'display_name' => 'No Access',
                'description' => '',
                'permissions' => [ ]
            ],
            [
                'name' => 'administrator-view-only',
                'display_name' => 'Administrator - View Only',
                'description' => 'A special administrative account where you can view the admin but not perform actions',
                'permissions' => [ ]
            ],
            [
                'name' => 'no-access',
                'display_name' => 'No Access',
                'description' => '',
                'permissions' => [ ]
            ],
            [
                'name'         => 'practice-lead',
                'display_name' => 'Program Lead',
                'description'  => 'The provider that created the practice.',
                'permissions'  => [
                    'practice-manage',
                    'observations-view',
                    'observations-create',
                    'users-view-all',
                    'users-view-self',
                ],
            ],
            [
                'name'         => 'registered-nurse',
                'display_name' => 'Registered Nurse',
                'description'  => 'A nurse that belongs to a practice and not our care center.',
                'permissions'  => [
                    'care-plan-approve',
                    'observations-view',
                    'observations-create',
                    'users-view-all',
                    'users-view-self',
                ],
            ],
            [
                'name'         => 'specialist',
                'display_name' => 'Specialist',
                'description'  => 'An outside specialist doctor.',
                'permissions'  => [
                    'observations-view',
                    'observations-create',
                    'users-view-all',
                    'users-view-self',
                ],
            ],
            [
                'name'         => 'salesperson',
                'display_name' => 'Salesperson',
                'description'  => 'A Salesperson',
                'permissions'  => [
                    'use-onboarding',
                ],
            ],
            [
                'name'         => 'care-ambassador',
                'display_name' => 'Care Ambassador',
                'description'  => 'Makes calls to enroll patients.',
                'permissions'  => [
                    'use-enrollment-center',
                ],
            ],
            [
                'name'         => 'med_assistant',
                'display_name' => 'Medical Assistant',
                'description'  => 'CCM Countable.',
                'permissions'  => [
                    'care-plan-approve',
                    'users-view-all',
                    'users-view-self',
                ],
            ],
            [
                'name'         => 'provider',
                'display_name' => 'Provider',
                'description'  => 'Provider',
                'permissions'  => [
                    'care-plan-approve',
                    'users-view-all',
                    'users-view-self',
                    'observations-create',
                    'observations-view',
                    'read-practice-chargeable-service',
                ],
            ],
            [
                'name'         => 'care-center',
                'display_name' => 'Care Center',
                'description'  => 'CLH Nurses, the ones who make calls to patients. CCM countable.',
                'permissions'  => [
                    'care-plan-qa-approve',
                    'users-view-all',
                    'users-view-self',
                    'activities-view',
                    'activities-pagetimer-view',
                    'apikeys-view',
                    'locations-view',
                    'observations-create',
                    'observations-edit',
                    'observations-view',
                    'programs-view',
                    'roles-view',
                    'roles-permissions-view',
                    'rules-engine-view',
                    'users-create',
                ],
            ],
            [
                'name'         => 'saas-admin',
                'display_name' => 'SAAS Admin',
                'description'  => 'An admin for CPM Software-As-A-Service.',
                'permissions'  => [

                ],
            ],
        ];
    }

    public function giveAdminsAllPermissions($roleName)
    {
        $adminRole = Role::whereName($roleName)->first();

        $permissions = Permission::where('name', '!=', 'care-plan-approve')
            ->get();

        $adminRole->perms()->sync($permissions->pluck('id')->all());
    }
}
