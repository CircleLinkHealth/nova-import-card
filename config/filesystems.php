<?php

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

    'default' => env('FILESYSTEM_DRIVER', 'local'),

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
    */

    'disks' => [

        'google' => [
            'driver'       => 'google',
            'clientId'     => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folderId'     => env('GOOGLE_DRIVE_FOLDER_ID'),
        ],

        //The directory where Practices deposit CCDAs on the Worker environment
        'ccdas'  => [
            'driver' => 'local',
            'root'   => env('CCDA_DROPBOX_PATH') ?? null,
        ],

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        'storage' => [
            'driver' => 'local',
            'root'   => storage_path(),
        ],

        'cloud' => [
            'driver' => 's3',
            'key'    => env('S3_CPM_STORAGE_KEY'),
            'secret' => env('S3_CPM_STORAGE_SECRET'),
            'region' => env('S3_CPM_STORAGE_REGION'),
            'bucket' => env('S3_CPM_STORAGE_BUCKET_NAME'),
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('OPCACHE_URL') . '/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key'    => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        'rackspace' => [
            'driver'    => 'rackspace',
            'username'  => 'your-username',
            'key'       => 'your-key',
            'container' => 'your-container',
            'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'IAD',
            'url_type'  => 'publicURL',
        ],

        'media' => [
            'driver' => 's3',
            'key'    => env('S3_CPM_STORAGE_KEY'),
            'secret' => env('S3_CPM_STORAGE_SECRET'),
            'region' => env('S3_CPM_STORAGE_REGION'),
            'bucket' => env('S3_CPM_STORAGE_BUCKET_NAME'),
        ],

        'backup' => [
            'driver' => 's3',
            'key'    => env('S3_CPM_STORAGE_KEY'),
            'secret' => env('S3_CPM_STORAGE_SECRET'),
            'region' => env('S3_CPM_STORAGE_REGION'),
            'bucket' => env('S3_CPM_STORAGE_BUCKET_NAME'),
            'root'   => 'backup',
        ],
    ],

];
