<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use CircleLinkHealth\Core\Facades\Connection;
//use CircleLinkHealth\Core\Facades\SchemaGrammar;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseServiceProvider as LaravelDatabaseServiceProvider;

class DatabaseServiceProvider extends LaravelDatabaseServiceProvider
{
    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            $dbm = new DatabaseManager($app, $app['db.factory']);

            //Extend to include the custom connection (MySql in this example)
            $dbm->extend('mysql', function ($config, $name) use ($app) {
                //Create default connection from factory
                $connection = $app['db.factory']->make($config, $name);
                //Instantiate our connection with the default connection data
                return new Connection(
                    $connection->getPdo(),
                    $connection->getDatabaseName(),
                    $connection->getTablePrefix(),
                    $config
                );
            });

            return $dbm;
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });
    }
}
