<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcdaParserProcessorPhp\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CcdaParse extends Command
{
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
     *      - CCDA_PARSER_DB_DATABASE=ccda-parser
     *      - CCDA_PARSER_DB_USERNAME=root
     *      - CCDA_PARSER_DB_PASSWORD=
     *  - php artisan ccd:parse 101 ./vendor/circlelinkhealth/ccda-parser-processor-php/nodejs/samples/nist.xml
     *
     * @var string
     */
    protected $signature = 'ccd:parse {ccdaId} {inputPath} {outputPath?}';

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
        $this->info('Ready to spawn nodejs process');
        $path       = dirname(__FILE__) . '../../../nodejs/index.js';
        $ccdaId     = $this->argument('ccdaId');
        $inputPath  = $this->argument('inputPath');
        $outputPath = $this->hasArgument('outputPath')
            ? $this->argument('outputPath')
            : null;
        $cmd        = "node $path" . " $ccdaId $inputPath" . (null !== $outputPath
                ? " $outputPath"
                : '');
        $process    = new Process($cmd);
        $process->run(function ($type, $buffer) {
            if ('err' === $type) {
                $this->error($buffer);
            } else {
                $this->info($buffer);
            }
        });
    }
}
