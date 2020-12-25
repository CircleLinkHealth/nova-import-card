<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use Illuminate\Console\Command;

class CreateOpPreloadPreloadFilePlaceholder extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the OP cache file in EFS, so that php.ini does not fail';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opcache:create-placeholder';

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
        $this->warn('Running as user '.get_current_user());
        $path = config('laraload.output');
        $this->line('Evaluating if file exists at '.realpath($path));

        if (file_exists($path)) {
            $this->warn('File already exists. Bailing.');

            return 0;
        }

        $this->warn("Creating `$path`");
        $created = file_put_contents($path, '<?php');

        if ((bool) $created) {
            $this->line("Created `$path`");

            return 0;
        }

        $this->error("Could not create `$path`");

        return 0;
    }
}
