<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$useLambdaStorage = env('USE_LAMBDA_STORAGE', false);
$lambdaRoot       = env('LAMBDA_STORAGE_ROOT', '/mnt/local');

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'storage'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'google' => [
            'driver'       => 'google',
            'clientId'     => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folderId'     => env('GOOGLE_DRIVE_FOLDER_ID'),
        ],

        'local' => [
            'driver' => 'local',
            'root'   => $useLambdaStorage ? ($lambdaRoot.'/app') : storage_path('app'),
        ],

        'storage' => [
            'driver' => 'local',
            'root'   => $useLambdaStorage ? $lambdaRoot : storage_path(),
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver'   => 's3',
            'key'      => env('AWS_ACCESS_KEY_ID'),
            'secret'   => env('AWS_SECRET_ACCESS_KEY'),
            'region'   => env('AWS_DEFAULT_REGION'),
            'bucket'   => env('AWS_BUCKET'),
            'url'      => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],

        'media' => [
            'driver' => 's3',
            'key'    => env('S3_CPM_STORAGE_KEY'),
            'secret' => env('S3_CPM_STORAGE_SECRET'),
            'region' => env('S3_CPM_STORAGE_REGION'),
            'bucket' => env('S3_CPM_STORAGE_BUCKET_NAME'),
        ],

        'secrets' => [
            'driver' => 's3',
            'key'    => env('S3_SECRETS_KEY'),
            'secret' => env('S3_SECRETS_SECRET'),
            'region' => env('S3_SECRETS_REGION'),
            'bucket' => env('S3_SECRETS_BUCKET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
