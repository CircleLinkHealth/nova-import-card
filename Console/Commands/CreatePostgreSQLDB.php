<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class CreatePostgreSQLDB extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new postresql database schema based on the database config file';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postgresql:createdb {dbname?} {host?} {port?} {username?} {password?} {encoding?}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $schemaName = $this->argument('dbname')
            ?: config('database.connections.pgsql.database');
        $host = $this->argument('host')
            ?: config('database.connections.pgsql.host');
        $port = $this->argument('port')
            ?: config('database.connections.pgsql.port');
        $username = $this->argument('username')
            ?: config('database.connections.pgsql.username');
        $password = $this->argument('password')
            ?: config('database.connections.pgsql.password');
        $encoding = $this->argument('encoding')
            ?: config('database.connections.pgsql.charset');

        if ( ! $schemaName) {
            throw new \Exception('Could not create database as schema name was not specified.');
        }

        try {
            $pdo = $this->getPDOConnection($host, $port, $username, $password);

            $dbExists = $this->dbExists($pdo, $schemaName);

            if ($dbExists) {
                $this->warn("Database $schemaName already exists. Doing nothing.");

                return;
            }

            //create the DB
            $pdo->exec("CREATE DATABASE $schemaName WITH ENCODING='$encoding';");

            if ( ! $this->dbExists($pdo, $schemaName)) {
                throw new \Exception("Could not create database $schemaName");
            }

            $this->line("Successfully created database $schemaName.");
        } catch (PDOException $exception) {
            $this->error(sprintf('Failed to create %s database, %s', $schemaName, $exception->getMessage()));
        }
    }

    private function dbExists($pdo, $schemaName)
    {
        return (bool) $pdo->query(
            sprintf(
                "SELECT datname FROM pg_database WHERE datname='%s';",
                $schemaName
            ),
            PDO::FETCH_ASSOC
        )->fetch();
    }

    /**
     * @param string $host
     * @param int    $port
     * @param string $username
     * @param string $password
     *
     * @return PDO
     */
    private function getPDOConnection($host, $port, $username, $password)
    {
        return new PDO(sprintf('pgsql:host=%s;port=%d;user=%s;password=%s;', $host, $port, $username, $password));
    }
}
