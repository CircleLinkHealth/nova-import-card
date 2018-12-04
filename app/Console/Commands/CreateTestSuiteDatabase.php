<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class CreateTestSuiteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a mysql database for the test suite.';

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
        $database = config('database.connections.test_suite.database');

        if ( ! $database) {
            $this->info('Skipping creation of database as env(TEST_SUITE_DB_DATABASE) is empty');

            return;
        }

        try {
            $pdo = $this->getPDOConnection(config('database.connections.test_suite.host'),
                config('database.connections.test_suite.port'), config('database.connections.test_suite.username'),
                config('database.connections.test_suite.password'));

            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET %s COLLATE %s;',
                $database,
                config('database.connections.test_suite.charset'),
                config('database.connections.test_suite.collation')
            ));

            $this->info(sprintf('Successfully created %s database', $database));
        } catch (PDOException $exception) {
            $this->error(sprintf('Failed to create %s database, %s', $database, $exception->getMessage()));
        }
    }

    /**
     * @param  string $host
     * @param  integer $port
     * @param  string $username
     * @param  string $password
     *
     * @return PDO
     */
    private function getPDOConnection($host, $port, $username, $password)
    {
        return new PDO(sprintf('mysql:host=%s;port=%d;', $host, $port), $username, $password);
    }
}
