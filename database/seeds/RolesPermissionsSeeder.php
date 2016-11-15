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
