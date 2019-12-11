<?php

namespace CcdaParserProcessorPhp\Console\Commands;

use Illuminate\Console\Command;

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
        $this->info('TODO');
    }
}