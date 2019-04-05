<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Exceptions\FileNotFoundException;
use Illuminate\Console\Command;

class StoreRelease extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store the release archive to git.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:store';

    /**
     * Create a new command instance.
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
        if ( ! app()->environment('staging')) {
            $this->error('This command can only run on staging');

            return;
        }

        $basePath = base_path();

        chdir($basePath);

        //the name of the build. Awkwardly named release
        $buildFileName = 'release.tar.gz';

        //we'll move the zipped build in this dir to setup git repo and push it to remote repo
        $buildDir = 'releases';

        if ( ! file_exists($buildFileName)) {
            throw new FileNotFoundException("`$buildFileName` not found in ".getcwd(), 500);
        }

        if ( ! file_exists($buildDir)) {
            mkdir($buildDir);
        }

        chdir($buildDir);

        if ( ! file_exists('.git')) {
            $initGit = $this->runCommand(
                'git init'
            );

            $this->runCommand('git remote add origin git@github.com:CircleLinkHealth/cpm-releases.git');
        }

        chdir($basePath);

        $moved = rename($buildFileName, getcwd().'/releases/'.$buildFileName);

        if ( ! $moved) {
            throw new \Exception("Could not move `$buildFileName` into `releases/$buildFileName`", 500);
        }

        chdir($buildDir);

        $version = \Version::format('compact');
        $command = "git add $buildFileName && git commit -m '$version' && git push -f -u origin master";

        $this->runCommand(
            $command
        );

        chdir($basePath);

        $this->runCommand("rm -rf $buildDir");
    }
}
