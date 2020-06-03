<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcdaParserProcessorPhp\Console\Commands;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CcdaParse extends Command
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse a CCD using bluebutton-js';

    /**
     * The name and signature of the console command.
     *
     * Example:
     *  - Need these set:
     *      - CCDA_PARSER_STORE_RESULTS_IN_DB=true
     *      - CCDA_PARSER_DB_JSON_TABLE=ccdas-json
     *      - CCDA_PARSER_DB_CONNECTION=mysql
     *  - php artisan ccd:parse 101 ./vendor/circlelinkhealth/ccda-parser-processor-php/nodejs/samples/nist.xml
     *
     * @var string
     */
    protected $signature = 'ccd:parse {ccdaId} {inputPath} {outputPath?} {--force}';

    /**
     * Create a new command instance.
     */
    public function __construct(Config $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Ready to spawn nodejs process');

        $process = Process::fromShellCommandline($this->prepareCommand());
        $process->setTimeout(60 * 20); //20 minutes
        $process->run(
            function ($type, $buffer) {
                if ('err' === $type) {
                    $this->error($buffer);
                } else {
                    $this->info($buffer);
                }
            },
            $this->valuesToInject()
        );
    }

    private function prepareCommand()
    {
        $cmdArgs = [
            'node "$path"',
            '--db-host="$dbHost"',
            '--db-port="$dbPort"',
            '--db-name="$dbDatabase"',
            '--db-username="$dbUsername"',
            '--db-password="$dbPassword"',
            '--db-json-table="$dbJsonTable"',
            '--store-results-in-db="$storeResultsInDb"',
            '--ccda-id="$ccdaId"',
            '--ccda-xml-path="$inputPath"',
        ];

        if ($this->argument('outputPath')) {
            $cmdArgs[] = '--ccda-json-target-path="$outputPath"';
        }

        if ($this->hasOption('force')) {
            $cmdArgs[] = '--force="true"';
        }

        return implode(' ', $cmdArgs);
    }

    private function valuesToInject()
    {
        $dbConnection     = $this->config->get('ccda-parser.db_connection');
        $dbConnectionData = $this->config->get("database.connections.$dbConnection");

        $args = [
            'path'             => dirname(__FILE__).'/../../nodejs/index.js',
            'storeResultsInDb' => true === $this->config->get('ccda-parser.store_results_in_db') ? 'true' : 'false',
            'dbJsonTable'      => $this->config->get('ccda-parser.db_table'),
            'dbHost'           => $dbConnectionData['host'],
            'dbPort'           => $dbConnectionData['port'],
            'dbDatabase'       => $dbConnectionData['database'],
            'dbUsername'       => $dbConnectionData['username'],
            'dbPassword'       => $dbConnectionData['password'],
            'ccdaId'           => $this->argument('ccdaId'),
            'inputPath'        => $this->argument('inputPath'),
        ];

        if ($this->argument('outputPath')) {
            $args['outputPath'] = $this->argument('outputPath');
        }

        return $args;
    }
}
