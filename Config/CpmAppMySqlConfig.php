<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Config;

use Illuminate\Support\Str;
use PDO;

class CpmAppMySqlConfig
{
    public static function toArray()
    {
        $mysqlDBName = env('DB_DATABASE', 'nothing');

        if ('nothing' === $mysqlDBName) {
            $mysqlDBName = Str::snake(getenv('HEROKU_BRANCH'));
        }

        if (getenv('CI')) {
            $mysqlDBName = getenv('HEROKU_TEST_RUN_ID');
        }

        $mysqlConfig = [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
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
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ];

        if (true === env('MYSQL_CLUSTER_MODE')) {
            $mysqlConfig['read'] = [
                'host' => explode(',', env('MYSQL_CLUSTER_READ_HOSTS')),
            ];
            $mysqlConfig['write'] = [
                'host' => explode(',', env('MYSQL_CLUSTER_WRITE_HOSTS')),
            ];
            $mysqlConfig['sticky'] = true;
        } else {
            $mysqlConfig['host'] = env('DB_HOST', '127.0.0.1');
        }

        return $mysqlConfig;
    }
}
