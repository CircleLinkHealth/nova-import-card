<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;


class PermissionsConfig extends Seeder {

    var $roles = array();
    var $permissions = array();
    var $msg = '';

    public function run()
    {

        // init
        $this->msg = 'This should always stay in sync with git branch, and all permissions arrays ALWAYS LISTING IN ALPHABETICAL ORDER!important! for organization' . PHP_EOL.PHP_EOL;
        echo 'Start role/permissions sync.' .$this->msg. PHP_EOL.PHP_EOL;

        // permissions KEEP EVERYTHING ALPHABETICAL!!
        $this->permissions = array(
            'activities-manage' => array('display_name' => 'Activities Manage', 'description' => '',),
            'activities-view' => array('display_name' => 'Activities View', 'description' => '',),
            'activities-pagetimer-manage' => array('display_name' => 'Time Tracking Manage', 'description' => '',),
            'activities-pagetimer-view' => array('display_name' => 'Time Tracking View', 'description' => '',),
            'admin-access' => array('display_name' => 'Admin Access', 'description' => '',),
            'app-config-manage' => array('display_name' => 'App Config Manage', 'description' => '',),
            'app-config-view' => array('display_name' => 'App Config View', 'description' => '',),
            'apikeys-manage' => array('display_name' => 'API Manage', 'description' => '',),
            'apikeys-view' => array('display_name' => 'API View', 'description' => '',),
            'locations-manage' => array('display_name' => 'Locations Manage', 'description' => '',),
            'locations-view' => array('display_name' => 'Locations View', 'description' => '',),
            'observations-create' => array('display_name' => 'Observations Create', 'description' => '',),
            'observations-destroy' => array('display_name' => 'Observations Destroy', 'description' => '',),
            'observations-edit' => array('display_name' => 'Observations Edit', 'description' => '',),
            'observations-view' => array('display_name' => 'Observations View', 'description' => '',),
            'practice-manage' => array('display_name' => 'Practice Manage', 'description' => 'Can Update or Delete a Practice.',),
            'programs-manage' => array('display_name' => 'Programs Manage', 'description' => '',),
            'programs-view' => array('display_name' => 'Programs View', 'description' => '',),
            'roles-manage' => array('display_name' => 'Roles Manage', 'description' => '',),
            'roles-view' => array('display_name' => 'Roles View', 'description' => '',),
            'roles-permissions-manage' => array('display_name' => 'Roles Permissions Manage', 'description' => '',),
            'roles-permissions-view' => array('display_name' => 'Roles Permissions View', 'description' => '',),
            'rules-engine-manage' => array('display_name' => 'Rules Engine Manage', 'description' => '',),
            'rules-engine-view' => array('display_name' => 'Rules Engine View', 'description' => '',),
            'users-create' => array('display_name' => 'User Create New User', 'description' => '',),
            'users-edit-all' => array('display_name' => 'User Edit All', 'description' => '',),
            'users-edit-self' => array('display_name' => 'User Edit Self', 'description' => '',),
            'users-view-all' => array('display_name' => 'User View All', 'description' => '',),
            'users-view-self' => array('display_name' => 'User View Self', 'description' => '',),

            //CCD API Permissions
            'post-ccd-to-api' => array('display_name' => 'POST CCDs to API', 'description' => 'Can POST CCDs to our API.',),
            'query-api-for-patient-data' => array('display_name' => 'Query API for Patient Data', 'description' => 'Can POST CCDs to our API.',),

            //Importer Permissions
            'ccd-import' => array('display_name' => 'Import CCDs', 'description' => 'Can use the CCD Importer.',),



        );

        // roles KEEP EVERYTHING ALPHABETICAL!!
        //$this->roles = array();
        $this->roles = array(
            'administrator' => array(
                'display_name' => 'Administrator',
                'description' => 'Administrator',
                'permissions' => array(
                    // administrator will always get all permissions
                )
            ),
            'api-ccd-vendor' => array(
                'display_name' => 'API CCD Vendor',
                'description' => 'Is able to post CCDs to our API',
                'permissions' => array(
                    'post-ccd-to-api'
                )
            ),
            'api-data-consumer' => array(
                'display_name' => 'API Data Consumer',
                'description' => 'Is able to receive PDF Reports and CCM Time from our API',
                'permissions' => array(
                    'query-api-for-patient-data'
                )
            ),
            'aprima-api-location' => array(
                'display_name' => 'API Data Consumer and CCD Vendor.',
                'description' => 'This role is JUST FOR APRIMA! Is able to receive PDF Reports and CCM Time from our API. Is able to post CCDs to our API.',
                'permissions' => array(
                    'post-ccd-to-api',
                    'query-api-for-patient-data'
                )
            ),
            'care-center'   => array(
                'display_name' => 'Care Center',
                'description' => 'Care Center',
                'permissions' => array(
                    'activities-pagetimer-view',
                    'activities-view',
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
                    'users-view-all',
                    'users-view-self'
                )
            ),
            'participant'   => array(
                'display_name' => 'Participant',
                'description' => 'Participant',
                'permissions' => array(
                    'observations-create',
                    'observations-view',
                    'users-view-self'
                )
            ),
            'practice-lead' => array(
                'display_name' => 'Program Lead',
                'description' => 'The provider that created the practice.',
                'permissions' => array(
                    'practice-manage',
                    'observations-view',
                    'observations-create',
                    'users-view-all',
                    'users-view-self'
                )
            ),
            'provider'      => array(
                'display_name' => 'Provider',
                'description' => 'Provider',
                'permissions' => array(
                    'observations-view',
                    'observations-create',
                    'users-view-all',
                    'users-view-self'
                )
            ),
            'no-ccm-care-center' => array(
                'display_name' => 'Care Center',
                'description' => 'Care Center',
                'permissions' => array(
                    'activities-manage',
                    'activities-view',
                    'activities-pagetimer-manage',
                    'activities-pagetimer-view',
                    //'admin-access',
                    'app-config-manage',
                    'app-config-view',
                    'apikeys-manage',
                    'apikeys-view',
                    'locations-manage',
                    'locations-view',
                    'observations-create',
                    'observations-destroy',
                    'observations-edit',
                    'observations-view',
                    'programs-manage',
                    'programs-view',
                    'roles-manage',
                    'roles-view',
                    'roles-permissions-manage',
                    'roles-permissions-view',
                    'rules-engine-manage',
                    'rules-engine-view',
                    'users-create',
                    'users-edit-all',
                    'users-edit-self',
                    'users-view-all',
                    'users-view-self',
                    'post-ccd-to-api',
                    'query-api-for-patient-data',
                    'ccd-import'
                )
            ),
            'med_assistant' => array(
                'display_name' => 'Medical Assistant',
                'description' => '',
                'permissions' => array(
                    'users-view-all',
                    'users-view-self'
                )
            ),
            'no-access' => array(
                'display_name' => 'No Access',
                'description' => '',
                'permissions' => array(
                )
            ),
            'office_admin' => array(
                'display_name' => 'Office Admin',
                'description' => '',
                'permissions' => array(
                    'users-view-all',
                    'users-view-self'
                )
            ),
            'viewer' => array(
                'display_name' => 'Viewer',
                'description' => '',
                'permissions' => array(
                    'users-view-all',
                    'users-view-self'
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