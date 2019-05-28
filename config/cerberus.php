<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Cerberus Role Model
    |--------------------------------------------------------------------------
    |
    | This is the Role model used by Cerberus to create correct relations.  Update
    | the role if it is in a different namespace.
    |
    */
    'role' => 'CircleLinkHealth\Customer\Entities\Role',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Roles Table
    |--------------------------------------------------------------------------
    |
    | This is the roles table used by Cerberus to save roles to the database.
    |
    */
    'roles_table' => 'lv_roles',

    /*
    |--------------------------------------------------------------------------
    | Cerberus role foreign key
    |--------------------------------------------------------------------------
    |
    | This is the role foreign key used by Cerberus to make a proper
    | relation between permissions and roles & roles and users
    |
    */
    'role_foreign_key' => 'role_id',

    /*
    |--------------------------------------------------------------------------
    | Site role foreign key
    |--------------------------------------------------------------------------
    |
    | This is the site foreign key used by Cerberus to make a proper
    | relation between permissions and roles, roles and users and users and sites
    |
    */
    'site_foreign_key' => 'program_id',

    /*
    |--------------------------------------------------------------------------
    | Application User Model
    |--------------------------------------------------------------------------
    |
    | This is the User model used by Cerberus to create correct relations.
    | Update the User if it is in a different namespace.
    |
    */
    'user' => 'CircleLinkHealth\Customer\Entities\User',

    /*
    |--------------------------------------------------------------------------
    | Application Users Table
    |--------------------------------------------------------------------------
    |
    | This is the users table used by the application to save users to the
    | database.
    |
    */
    'users_table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Cerberus role_user Table
    |--------------------------------------------------------------------------
    |
    | This is the role_user table used by Cerberus to save assigned roles to the
    | database.
    |
    */
    'role_user_site_table' => 'practice_role_user',

    /*
    |--------------------------------------------------------------------------
    | Cerberus user foreign key
    |--------------------------------------------------------------------------
    |
    | This is the user foreign key used by Cerberus to make a proper
    | relation between roles and users
    |
    */
    'user_foreign_key' => 'user_id',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Permission Model
    |--------------------------------------------------------------------------
    |
    | This is the Permission model used by Cerberus to create correct relations.
    | Update the permission if it is in a different namespace.
    |
    */
    'permission' => 'CircleLinkHealth\Customer\Entities\Permission',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Permissions Table
    |--------------------------------------------------------------------------
    |
    | This is the permissions table used by Cerberus to save permissions to the
    | database.
    |
    */
    'permissions_table' => 'lv_permissions',

    /*
    |--------------------------------------------------------------------------
    | Cerberus permission_role Table
    |--------------------------------------------------------------------------
    |
    | This is the permission_role table used by Cerberus to save relationship
    | between permissions and roles to the database.
    |
    */
    'permissibles' => 'permissibles',

    /*
    |--------------------------------------------------------------------------
    | Cerberus permission foreign key
    |--------------------------------------------------------------------------
    |
    | This is the permission foreign key used by Cerberus to make a proper
    | relation between permissions and roles
    |
    */
    'permission_foreign_key' => 'permission_id',

    /*
    |--------------------------------------------------------------------------
    | Sites Model
    |--------------------------------------------------------------------------
    |
    | This is the Sites model used by Cerberus to create correct relations between roles, users and sites.
    | Update the permission if it is in a different namespace.
    |
    */
    'site' => 'CircleLinkHealth\Customer\Entities\Practice',

    /*
    |--------------------------------------------------------------------------
    | Cerberus Sites Table
    |--------------------------------------------------------------------------
    |
    | This is the permissions table used by Cerberus to save permissions to the
    | database.
    |
    */
    'sites_table' => 'practices',
];
