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
        foreach ($this->permissions() as $perm) {
            Permission::updateOrCreate($perm);
        }

        foreach ($this->roles() as $attr) {
            $permissionsArr = $attr['permissions'];

            unset($attr['permissions']);

            $role = Role::updateOrCreate($attr);

            $permissionIds = Permission::whereIn('name', $permissionsArr)
                ->pluck('id')->all();

            $role->perms()->sync($permissionIds);
        }

        $this->giveAdminsAllPermissions();

        $this->command->info('That\'s all folks!');
    }

    public function permissions()
    {
        return [
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
        ];
    }

    public function roles()
    {
        return [
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
        ];
    }

    public function giveAdminsAllPermissions()
    {
        $adminRole = Role::whereName('administrator')->first();

        $permissions = Permission::where('name', '!=', 'care-plan-approve')
            ->get();

        $adminRole->perms()->sync($permissions->pluck('id')->all());
    }
}
