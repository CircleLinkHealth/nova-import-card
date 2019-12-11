<?php

namespace CircleLinkHealth\CcdaParserProcessorPhp\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CcdaParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccd:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse a CCD using bluebutton-js';

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
        $path = dirname(__FILE__) . '../../../nodejs/index.js';
        $process = new Process("node $path");
        $result = $process->run();
        if ($result !== 0) {
            $this->error($process->getErrorOutput());
        }
        else {
            $this->info($process->getOutput());
        }
    }
}