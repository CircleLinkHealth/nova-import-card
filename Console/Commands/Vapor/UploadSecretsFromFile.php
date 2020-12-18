<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands\Vapor;

use CircleLinkHealth\Core\Traits\RunsCommands;
use Dotenv\Dotenv;
use Illuminate\Console\Command;

class UploadSecretsFromFile extends Command
{
    use RunsCommands;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload secrets from a .env file to vapor';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpmvapor:uploadsecrets {--path= : The full path to the .env file\'s dir.}
                                                   {--file= : The filename of the .env file.}
                                                   {--environment= : The environment to upload to.}
                                                   ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $secrets = collect((Dotenv::createImmutable($this->option('path'), $this->option('file')))->load());
    
        $bar = $this->output->createProgressBar($secrets->count());
    
        $bar->start();
        
        $secrets->each(function ($secret, $name) use ($bar){
            file_put_contents($tmp = storage_path(now()->timestamp.$name), $secret);
            $this->runCpmCommand("vapor secret {$this->option('environment')} --file=$tmp --name=$name");
            $this->runCpmCommand("rm -rf $tmp");
            $bar->advance();
        });
    
        $bar->finish();
    }
}
