<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | Default module namespace.
    |
    */

    'namespace' => 'CircleLinkHealth',

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | Default module stubs.
    |
    */

    'stubs' => [
        'enabled' => false,
        'path'    => base_path().'/vendor/nwidart/laravel-modules/src/Commands/stubs',
        'files'   => [
            'composer' => 'composer.json',
        ],
        'replacements' => [
            'composer' => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
            ],
        ],
        'gitkeep' => true,
    ],
    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Modules path
        |--------------------------------------------------------------------------
        |
        | This path used for save the generated module. This path also will be added
        | automatically to list of scanned folders.
        |
        */

        'modules' => base_path('CircleLinkHealth'),
        /*
        |--------------------------------------------------------------------------
        | Modules assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the modules assets path.
        |
        */

        'assets' => public_path('modules'),
        /*
        |--------------------------------------------------------------------------
        | The migrations path
        |--------------------------------------------------------------------------
        |
        | Where you run 'module:publish-migration' command, where do you publish the
        | the migration files?
        |
        */

        'migration' => base_path('database/migrations'),
        /*
        |--------------------------------------------------------------------------
        | Generator path
        |--------------------------------------------------------------------------
        | Customise the paths where the folders will be generated.
        | Set the generate key to false to not generate that folder
        */
        'generator' => [
            'config'        => ['path' => 'Config', 'generate' => false],
            'command'       => ['path' => 'Console', 'generate' => false],
            'migration'     => ['path' => 'Database/Migrations', 'generate' => false],
            'seeder'        => ['path' => 'Database/Seeders', 'generate' => false],
            'factory'       => ['path' => 'Database/factories', 'generate' => false],
            'model'         => ['path' => 'Entities', 'generate' => false],
            'controller'    => ['path' => 'Http/Controllers', 'generate' => true],
            'filter'        => ['path' => 'Http/Middleware', 'generate' => false],
            'request'       => ['path' => 'Http/Requests', 'generate' => false],
            'provider'      => ['path' => 'Providers', 'generate' => false],
            'assets'        => ['path' => 'Resources/assets', 'generate' => true],
            'lang'          => ['path' => 'Resources/lang', 'generate' => false],
            'views'         => ['path' => 'Resources/views', 'generate' => false],
            'test'          => ['path' => 'Tests', 'generate' => false],
            'repository'    => ['path' => 'Repositories', 'generate' => false],
            'event'         => ['path' => 'Events', 'generate' => false],
            'listener'      => ['path' => 'Listeners', 'generate' => false],
            'policies'      => ['path' => 'Policies', 'generate' => false],
            'rules'         => ['path' => 'Rules', 'generate' => false],
            'jobs'          => ['path' => 'Jobs', 'generate' => false],
            'emails'        => ['path' => 'Emails', 'generate' => false],
            'notifications' => ['path' => 'Notifications', 'generate' => false],
            'resource'      => ['path' => 'Transformers', 'generate' => false],
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Scan Path
    |--------------------------------------------------------------------------
    |
    | Here you define which folder will be scanned. By default will scan vendor
    | directory. This is useful if you host the package in packagist website.
    |
    */

    'scan' => [
        'enabled' => false,
        'paths'   => [
            base_path('vendor/*/*'),
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Composer File Template
    |--------------------------------------------------------------------------
    |
    | Here is the config for composer.json file, generated by this package
    |
    */

    'composer' => [
        'vendor' => 'circlelinkhealth',
        'author' => [
            [
                'name'  => 'Antonis Antoniou',
                'email' => 'antonis@cirlelinkhealth.com',
                'role'  => 'Developer',
            ],
            [
                'name'  => 'Constantinos Kakoushias',
                'email' => 'constantinos@cirlelinkhealth.com',
                'role'  => 'Developer',
            ],
            [
                'name'  => 'Michalis Antoniou',
                'email' => 'mantoniou@cirlelinkhealth.com',
                'role'  => 'Lead Developer',
            ],
            [
                'name'  => 'Pangratios Cosma',
                'email' => 'pangratios@cirlelinkhealth.com',
                'role'  => 'Lead Developer',
            ],
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache' => [
        'enabled'  => false,
        'key'      => 'circlelinkhealth-modules',
        'lifetime' => 60,
    ],
    /*
    |--------------------------------------------------------------------------
    | Choose what laravel-modules will register as custom namespaces.
    | Setting one to false will require you to register that part
    | in your own Service Provider class.
    |--------------------------------------------------------------------------
    */
    'register' => [
        'translations' => false,
        /*
         * load files on boot or register method
         *
         * Note: boot not compatible with asgardcms
         *
         * @example boot|register
         */
        'files' => 'register',
    ],
];
