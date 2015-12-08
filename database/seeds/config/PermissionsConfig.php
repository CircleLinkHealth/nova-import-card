<?php

use App\WpUser;
use App\Permission;
use App\Role;
use App\WpUserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class PermissionsConfig extends Seeder {

    var $roles = array();
    var $permissions = array();
    var $msg = '';

    public function run()
    {

        // init
        $this->msg = 'This should always stay in sync with git branch, and all permissions ALWAYS LISTING IN ALPHABETICAL ORDER!!' . PHP_EOL.PHP_EOL;
        echo 'Start role/permissions sync.' .$this->msg. PHP_EOL.PHP_EOL;

        // permissions
        /*
         * activities-manage
         * activities-view
         * time-tracking-manage
         * time-tracking-view
         * api-manage
         * api-view
         * locations-manage
         * locations-view
         * programs-manage
         * programs-view
         * roles-manage
         * roles-view
         * roles-permissions-manage
         * roles-permissions-view
         * rules-engine-manage
         * rules-engine-view
         * user-create
         * user-edit-all
         * user-edit-self
         * user-view-all
         * user-view-self
         */
        $this->permissions = array(
            'activities-manage' => array(
                'display_name' => 'Activities Manage',
                'description' => '',
            ),
            'activities-view' => array(
                'display_name' => 'Activities View',
                'description' => '',
            ),
            'time-tracking-manage' => array(
                'display_name' => 'Time Tracking Manage',
                'description' => '',
            ),
            'time-tracking-view' => array(
                'display_name' => 'Time Tracking View',
                'description' => '',
            ),
            'api-manage' => array(
                'display_name' => 'API Manage',
                'description' => '',
            ),
            'api-view' => array(
                'display_name' => 'API View',
                'description' => '',
            ),
            'locations-manage' => array(
                'display_name' => 'Locations Manage',
                'description' => '',
            ),
            'locations-view' => array(
                'display_name' => 'Locations View',
                'description' => '',
            ),
            'programs-manage' => array(
                'display_name' => 'Programs Manage',
                'description' => '',
            ),
            'programs-view' => array(
                'display_name' => 'Programs View',
                'description' => '',
            ),
            'roles-manage' => array(
                'display_name' => 'Roles Manage',
                'description' => '',
            ),
            'roles-view' => array(
                'display_name' => 'Roles View',
                'description' => '',
            ),
            'roles-permissions-manage' => array(
                'display_name' => 'Roles Permissions Manage',
                'description' => '',
            ),
            'roles-permissions-view' => array(
                'display_name' => 'Roles Permissions View',
                'description' => '',
            ),
            'rules-engine-manage' => array(
                'display_name' => 'Rules Engine Manage',
                'description' => '',
            ),
            'rules-engine-view' => array(
                'display_name' => 'Rules Engine View',
                'description' => '',
            ),
            'user-create' => array(
                'display_name' => 'User Create New User',
                'description' => '',
            ),
            'user-edit-all' => array(
                'display_name' => 'User Edit All',
                'description' => '',
            ),
            'user-edit-self' => array(
                'display_name' => 'User Edit Self',
                'description' => '',
            ),
            'user-view-all' => array(
                'display_name' => 'User View All',
                'description' => '',
            ),
            'user-view-self' => array(
                'display_name' => 'User View Self',
                'description' => '',
            ),

        );

        // roles
        $this->roles = array(
            'administrator' => array(
                'display_name' => 'Administrator',
                'description' => 'Administrator',
                'permissions' => array(
                    // administrator will always get all permissions
                )
            ),
            'manager' => array(
                'display_name' => 'Manager',
                'description' => 'Manager',
                'permissions' => array(
                    'activities-view',
                    'locations-manage',
                    'locations-view',
                    'programs-manage',
                    'programs-view',
                    'user-create',
                    'user-edit-self',
                    'user-view-all',
                    'user-view-self'
                )
            ),
            'participant' => array(
                'display_name' => 'Participant',
                'description' => 'Participant',
                'permissions' => array(
                    'user-view-self',
                    'user-edit-self',
                )
            ),
            'provider' => array(
                'display_name' => 'Provider',
                'description' => 'Provider',
                'permissions' => array(
                    'activities-view',
                    'locations-manage',
                    'locations-view',
                    'user-create',
                    'user-edit-self',
                    'user-view-all',
                    'user-view-self'
                )
            ),
        );


        // -------------------------------------------------------

        $this->updateRolesAndPermissions();

    }


    public function updateRolesAndPermissions() {
        $permissions = $this->permissions;
        $roles = $this->roles;

        // first make sure all permissions are in database
        echo PHP_EOL.PHP_EOL . 'PERMISSIONS' . PHP_EOL.PHP_EOL;
        foreach($permissions as $permissionName => $permissionInfo) {
            echo $permissionName . PHP_EOL;
            // get permission and add id to array
            $permission = Permission::where('name', '=', $permissionName)->first();
            if(!empty($permission)) {
                $permissions[$permission->name]['id'] = $permission->id;
                $permission->description = $permissionInfo['description']; // update description
                $permission->save();
            } else {
                // permission not in db, add
                $permission = new Permission;
                $permission->name = $permissionName;
                $permission->display_name = $permissionInfo['display_name'];
                $permission->description = $permissionInfo['description'];
                $permission->save();
                echo 'added new permission - '.$permissionName.' (id[' . $permission->id . ']' . PHP_EOL;
                $permissions[$permission->name]['id'] = $permission->id;
            }
        }


        // remove any permissions that are no longer in permissions array
        $existingPermissions = Permission::all();
        if(!empty($existingPermissions)) {
            foreach($existingPermissions as $existingPermission) {
                if(!array_key_exists($existingPermission->name, $permissions)) {
                    $existingPermission->delete();
                    echo 'permission no longer exists, removing - '.$existingPermission->name . PHP_EOL;
                }
            }
        }

        // next make sure all roles are in the database
        echo PHP_EOL.PHP_EOL . 'ROLES' . PHP_EOL.PHP_EOL;
        foreach($roles as $roleName => $roleInfo) {
            echo PHP_EOL . $roleName . PHP_EOL;
            // get role and add id to array
            $role = Role::where('name', '=', $roleName)->first();
            if(!empty($role)) {
                $roles[$roleName]['id'] = $role->id;
                $role->description = $roleInfo['description']; // update description
                $role->save();
            } else {
                // permission not in db, add
                $role = new Role;
                $role->name = $roleName;
                $role->display_name = $roleInfo['display_name'];
                $role->description = $roleInfo['description'];
                $role->save();
                echo 'added new role - '.$roleName.' (id[' . $role->id . ']' . PHP_EOL;
                $permissions[$roleName]['id'] = $role->id;
            }

            // role permissions
            $rolePermissionIds = array();
            foreach($permissions as $key => $permission) {
                // administrator gets all
                if($roleName == 'administrator') {
                    $rolePermissionIds[] = $permission['id'];
                } else {
                    if(in_array($key, $roleInfo['permissions'])) {
                        $rolePermissionIds[] = $permission['id'];
                    }
                }
            }
            foreach($rolePermissionIds as $permissionId) {
                // administrator gets all
                echo ' id-' . $permissionId . PHP_EOL;
            }

            // sync role and permissions
            $role->perms()->sync($rolePermissionIds);
            echo 'synced permissions to role ' . $roleName . PHP_EOL;
        }

        // remove any roles that are no longer in roles array
        $existingRoles = Role::all();
        if($existingRoles->count() > 0) {
            foreach($existingRoles as $existingRole) {
                if(!array_key_exists($existingRole->name, $roles)) {
                    //dd($existingRole->users()->get());
                    //dd($existingRole->users()->sync([]));
                    $existingRole->users()->sync([]); // Delete relationship data
                    $existingRole->perms()->sync([]); // Delete relationship data
                    $existingRole->forceDelete();
                    echo 'role no longer exists, removing - '.$existingRole->name . PHP_EOL;
                }
            }
        }

        // end
        echo PHP_EOL.PHP_EOL.'End role/permissions sync.' .$this->msg. PHP_EOL.PHP_EOL;
    }

}