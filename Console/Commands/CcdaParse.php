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
     *      - CCDA_PARSER_DB_HOST=127.0.0.1
     *      - CCDA_PARSER_DB_PORT=3306
     *      - CCDA_PARSER_DB_DATABASE=cpm_local
     *      - CCDA_PARSER_DB_JSON_TABLE=ccdas-json
     *      - CCDA_PARSER_DB_USERNAME=root
     *      - CCDA_PARSER_DB_PASSWORD=
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

        $cmd = $this->prepareCommand();
        
        var_dump($cmd);

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(60 * 20); //20 minutes
        $process->run(function ($type, $buffer) {
            if ('err' === $type) {
                $this->error($buffer);
            } else {
                $this->info($buffer);
            }
        });
    }

    private function prepareCommand()
    {
        $path             = dirname(__FILE__).'/../../nodejs/index.js';
        $storeResultsInDb = $this->config->get('ccda-parser.store_results_in_db');
        $dbConnection     = $this->config->get('ccda-parser.db_connection');
        $dbJsonTable      = $this->config->get('ccda-parser.db_table');

        $dbConnectionData = $this->config->get("database.connections.$dbConnection");
        $dbHost           = $dbConnectionData['host'];
        $dbPort           = $dbConnectionData['port'];
        $dbDatabase       = $dbConnectionData['database'];
        $dbUsername       = $dbConnectionData['username'];
        $dbPassword       = $dbConnectionData['password'];

        $ccdaId    = $this->argument('ccdaId');
        $inputPath = $this->argument('inputPath');

        $cmdArgs[] = "node $path";
        $cmdArgs[] = "--db-host=$dbHost";
        $cmdArgs[] = "--db-port=$dbPort";
        $cmdArgs[] = "--db-name=$dbDatabase";
        $cmdArgs[] = "--db-username=$dbUsername";
        $cmdArgs[] = "--db-password=$dbPassword";
        $cmdArgs[] = "--db-json-table=$dbJsonTable";
        $cmdArgs[] = "--store-results-in-db=$storeResultsInDb";
        $cmdArgs[] = "--ccda-id=$ccdaId";
        $cmdArgs[] = "--ccda-xml-path=$inputPath";
        if ($this->hasArgument('outputPath')) {
            $outputPath = $this->argument('outputPath');
            $cmdArgs[]  = "--ccda-json-target-path=$outputPath";
        }
        if ($this->hasOption('force')) {
            $cmdArgs[] = '--force=true';
        }

        return implode(' ', $cmdArgs);
    }
}
