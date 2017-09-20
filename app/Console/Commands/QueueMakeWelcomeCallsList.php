<?php

namespace App\Console\Commands;

use App\Jobs\MakePhoenixHeartWelcomeCallList;
use Illuminate\Console\Command;

class QueueMakeWelcomeCallsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:welcomeCallList {practice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make welcome call lists for a practice';

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
        if ($this->argument('practice') == 'PHX') {
            dispatch(new MakePhoenixHeartWelcomeCallList());
        }
    }
}
