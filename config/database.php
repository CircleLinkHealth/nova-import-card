<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

// for heroku
use Illuminate\Support\Str;

if (getenv('DATABASE_URL')) {
    $pgsqlUrl = parse_url(getenv('DATABASE_URL'));

    $pgSqlhost     = $pgsqlUrl['host'];
    $pgSqlusername = $pgsqlUrl['user'];
    $pgSqlpassword = $pgsqlUrl['pass'];
    $pgSqldatabase = substr($pgsqlUrl['path'], 1);

    $psqlConfig = [
        'driver'         => 'pgsql',
        'host'           => $pgSqlhost,
        'port'           => env('DB_PORT', '5432'),
        'database'       => $pgSqldatabase,
        'username'       => $pgSqlusername,
        'password'       => $pgSqlpassword,
        'charset'        => 'utf8',
        'prefix'         => '',
        'prefix_indexes' => true,
        'schema'         => 'public',
        'sslmode'        => 'prefer',
    ];
}

if (getenv('CLEARDB_DATABASE_URL')) {
    $clearDBBUrl = parse_url(getenv('CLEARDB_DATABASE_URL'));

    $clearDBBhost     = $clearDBBUrl['host'];
    $clearDBBusername = $clearDBBUrl['user'];
    $clearDBBpassword = $clearDBBUrl['pass'];
    $clearDBBdatabase = substr($clearDBBUrl['path'], 1);

    $clearDBConfig = [
        'driver'         => 'mysql',
        'charset'        => 'utf8mb4',
        'collation'      => 'utf8mb4_unicode_ci',
        'prefix'         => '',
        'prefix_indexes' => true,
        'strict'         => false,
        'engine'         => null,
    ];

    $clearDBConfig['host']     = $clearDBBhost;
    $clearDBConfig['database'] = $clearDBBdatabase;
    $clearDBConfig['username'] = $clearDBBusername;
    $clearDBConfig['password'] = $clearDBBpassword;
}

// for heroku
if (getenv('REDIS_URL')) {
    $redisUrl = parse_url(getenv('REDIS_URL'));

    putenv('REDIS_HOST='.$redisUrl['host']);
    putenv('REDIS_PORT='.$redisUrl['port']);
    putenv('REDIS_PASSWORD='.$redisUrl['pass']);
}

$mysqlDBName = env('DB_DATABASE', 'nothing');

if ('nothing' === $mysqlDBName) {
    $mysqlDBName = Str::snake(getenv('HEROKU_BRANCH'));
}

if (getenv('CI')) {
    $mysqlDBName = getenv('HEROKU_TEST_RUN_ID');
}

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DATABASE_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'cleardb' => $clearDBConfig ?? [],

        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => $mysqlDBName,
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => false,
            'engine'         => null,
        ],

        'test_suite' => [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST', '127.0.0.1'),
            'port'        => env('DB_PORT', '3306'),
            'database'    => env('TEST_SUITE_DB_DATABASE', 'cpm_test_suite'),
            'username'    => env('DB_USERNAME', 'forge'),
            'password'    => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_unicode_ci',
            'prefix'      => '',
            'strict'      => false,
            'engine'      => null,
        ],

        'pgsql' => $psqlConfig ?? [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'schema'         => 'public',
            'sslmode'        => 'prefer',
        ],

        'sqlsrv' => [
            'driver'         => 'sqlsrv',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', 'localhost'),
            'port'           => env('DB_PORT', '1433'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => 'cpm_database', //same as AWV
        ],

        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        // need a specific connection for the pub/sub channel
        // otherwise, we get errors (tried both phpredis and predis)
        'pub_sub' => [
            'url'                => env('REDIS_URL'),
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => env('REDIS_DB', 0),
            'read_write_timeout' => -1,
        ],
    ],
];
