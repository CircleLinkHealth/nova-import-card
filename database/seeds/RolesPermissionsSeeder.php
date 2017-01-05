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
        $permissions = [
            [
                'name' => 'practice-manage',
                'display_name' => 'Practice Manage',
                'description' => 'Can Update or Delete a Practice.',
            ],
            [
                'name'         => 'use-onboarding',
                'display_name' => 'Use Onboarding without a code',
                'description'  => 'Can use Onboarding to set up a Practice.',
            ],
        ];

        $roles = [
            [
                'name'         => 'practice-lead',
                'display_name' => 'Program Lead',
                'description'  => 'The provider that created the practice.',
                'permissions'  => [
                    'practice-manage',
                    'observations-view',
                    'observations-create',
                    'users-view-all',
                    'users-view-self'
                ]
            ],
            [
                'name'         => 'registered-nurse',
                'display_name' => 'Registered Nurse',
                'description'  => 'A nurse that belongs to a practice and not our care center.',
                'permissions'  => [
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
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate($perm);
        }

        foreach ($roles as $attr) {
            $permissionsArr = $attr['permissions'];

            unset($attr['permissions']);

            $role = Role::updateOrCreate($attr);

            $permissionIds = Permission::whereIn('name', $permissionsArr)
                ->pluck('id')->all();

            $role->perms()->sync($permissionIds);
        }

        $this->command->info('That\'s all folks!');
    }
}
