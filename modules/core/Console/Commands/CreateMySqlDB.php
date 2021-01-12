<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class CreateMySqlDB extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new mysql database schema based on the database config file';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mysql:createdb {name?}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $schemaName = $this->argument('name') ?: config('database.connections.mysql.database');
        $charset    = config('database.connections.mysql.charset', 'utf8mb4');
        $collation  = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

        config(['database.connections.mysql.database' => null]);

        $query = "CREATE DATABASE IF NOT EXISTS `$schemaName` CHARACTER SET `$charset` COLLATE `$collation`;";

        $this->warn("Running `$query`");

        $this->executeSql($query);

        config(['database.connections.mysql.database' => $schemaName]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    private function executeSql(string $query)
    {
        try {
            $pdo = $this->getPDOConnection(
                config('database.connections.mysql.host'),
                config('database.connections.mysql.port'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password')
            );

            $pdo->exec($query);
        } catch (PDOException $exception) {
            $this->error($exception->getMessage());
        }
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
        return new PDO(sprintf('mysql:host=%s;port=%d;', $host, $port), $username, $password);
    }
}
