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
        $path       = dirname(__FILE__).'../../../nodejs/index.js';
        $ccdaId     = $this->argument('ccdaId');
        $inputPath  = $this->argument('inputPath');
        $outputPath = $this->hasArgument('outputPath')
            ? $this->argument('outputPath')
            : null;
        $cmd = "node $path"." $ccdaId $inputPath".(null !== $outputPath
                ? " $outputPath"
                : '');
        $process = new Process($cmd);
        $process->run(function ($type, $buffer) {
            if ('err' === $type) {
                $this->error($buffer);
            } else {
                $this->info($buffer);
            }
        });
    }
}
