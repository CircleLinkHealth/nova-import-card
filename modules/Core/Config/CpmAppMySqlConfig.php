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
        echo "\nCpmAppMySqlConfig [1]: $mysqlDBName\n";

        $ciVal = json_encode(getenv('CI'));
        echo "\nCpmAppMySqlConfig getenv('CI'): $ciVal\n";

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

        if (true === filter_var(env('MYSQL_CLUSTER_MODE'), FILTER_VALIDATE_BOOLEAN)) {
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

        $json_string = json_encode($mysqlConfig, JSON_PRETTY_PRINT);
        echo "\n$json_string\n";

        return $mysqlConfig;
    }
}
