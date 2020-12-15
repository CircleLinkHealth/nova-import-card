<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GiveMePaths extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:paths';

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
     * @return int
     */
    public function handle()
    {
        $cwd = getcwd();
        $this->info("getcwd(): $cwd");
        $storagePath = storage_path();
        $this->info("storage_path: $storagePath");
        $dirName = dirname(__FILE__);
        $this->info("dirname: $dirName");

        return 0;
    }
}
